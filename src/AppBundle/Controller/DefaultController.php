<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\FindSched;
use AppBundle\Entity\Companies;
use AppBundle\Entity\CompanyVessels;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(FindSched::class);
        $form->handleRequest($request);

        $schedules = [];
        $from_form = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $date = \DateTime::createFromFormat('F d, Y', $data['date']);

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery("
                SELECT
                    cv.id, c.id as com_id, cv.departTime, c.name as company, sp2.name as departPort,
                    cv.arriveTime, sp.name as arrivePort, cv.vesselType,
                    cv.passPriceRange, cv.vehiPriceRange, cv.name as vessel, dest.townCity as destCity,
                    dep.townCity as depCity
                FROM AppBundle:CompanyVessels cv
                    JOIN AppBundle:Companies c WITH c.id = cv.company
                    JOIN AppBundle:SeaPorts sp WITH sp.id = cv.arrivePort
                    JOIN AppBundle:SeaPorts sp2 WITH sp2.id = cv.departPort
                    JOIN AppBundle:TownCities dest WITH dest.id = sp.townCity
                    JOIN AppBundle:TownCities dep WITH dep.id = sp2.townCity
                    JOIN AppBundle:Distances d WITH cv.departPort = d.seaPort
                    JOIN AppBundle:TownCities tc2 WITH tc2.id = d.targetTownCity
                WHERE
                    tc2.townCity = :tcity
                    AND dest.province = :dprovince
                    AND ( cv.schedDay LIKE :sday OR cv.schedDay = 'Daily' )
            "); 
            
            $query->setParameter('tcity', $data['origin']->getTownCity());
            $query->setParameter('dprovince', $data['destination']->getProvince());
            $query->setParameter('sday', '%'.$date->format('D').'%');

            $results = $query->getScalarResult();
            $schedules = [];
            foreach ($results as $key => $result) {
                if ($result['destCity'] == $data['destination']->getTownCity()) {
                    array_unshift($schedules, $result);
                }
                else {
                    $schedules[] = $result;
                }
            }

            $from_form = true;
        }

        return $this->render('default/index.html.twig', [
            'form'      => $form->createView(),
            'schedules' => $schedules,
            'from_form' => $from_form
        ]);
    }

    public function getCoordinates($address) {
        $address = str_replace(" ", "+", $address) . "+Philippines";

        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";

        $response = file_get_contents($url);

        $json = json_decode($response,TRUE);

        if ($json['results']) {
            return $json['results'][0]['geometry']['location']['lat'].",".$json['results'][0]['geometry']['location']['lng'];
        }

        return '';
    }

    /**
     * @Route("/detail/{id}", name="detail")
     */
    public function detailAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("
                SELECT
                    cv.id, c.id as com_id, cv.departTime, c.name as company, sp2.name as departPort,
                    c.booksite, c.promoUrl as promo, c.officesUrl as offices,
                    cv.arriveTime, sp.name as arrivePort, cv.vesselType,
                    cv.passPriceRange, cv.vehiPriceRange, cv.name as vessel,
                    dep.townCity as depCity, dep.province as depProv,
                    dest.townCity as destCity, dest.province as destProv
                FROM AppBundle:CompanyVessels cv
                    JOIN AppBundle:Companies c WITH c.id = cv.company
                    JOIN AppBundle:SeaPorts sp WITH sp.id = cv.arrivePort
                    JOIN AppBundle:SeaPorts sp2 WITH sp2.id = cv.departPort
                    JOIN AppBundle:TownCities dest WITH dest.id = sp.townCity
                    JOIN AppBundle:TownCities dep WITH dep.id = sp2.townCity
                    JOIN AppBundle:Distances d WITH cv.departPort = d.seaPort
                    JOIN AppBundle:TownCities tc2 WITH tc2.id = d.targetTownCity
                WHERE
                    cv.id = :id
        "); 
        $query->setParameter('id', $id);
        $schedule = $query->getScalarResult()[0];

        $schedule['depCoor']  = $this->getCoordinates($schedule['depCity']." ".$schedule['depProv']);
        $schedule['destCoor'] = $this->getCoordinates($schedule['destCity']." ".$schedule['destProv']);

        $query = $em->createQuery("
            SELECT va.accomodation, va.price, va.features
            FROM AppBundle:VesselAccomodations va
            WHERE va.vessel = :id
        ");
        $query->setParameter('id', $id);
        $accomodations = $query->getScalarResult();

        $query = $em->createQuery("
            SELECT cb.address, cb.phone
            FROM AppBundle:CompanyBookings cb
            WHERE cb.company = :company
            AND
                (
                    LOWER(cb.address) like LOWER(:dep) OR
                    LOWER(cb.address) like LOWER(:prov)
                )
        ");
        $query->setParameter('company', $schedule['com_id']);
        $query->setParameter('dep', "%".$schedule['depCity']."%");
        $query->setParameter('prov', "%".$schedule['depProv']."%");
        $booking_offices = $query->getScalarResult();

        $offices = [];
        foreach ($booking_offices as $office) {
            $address = $office['address'];

            $office['coor'] = $this->getCoordinates($address);

            $offices[] = $office;
        }

        return $this->render('default/detail.html.twig', [
            'schedule'      => $schedule,
            'accomodations' => $accomodations,
            'booking_offices' => $offices
        ]);
    }

    /**
     * @Route("/company/{id}", name="company")
     */
    public function companyAction($id, Request $request)
    {
        $company = $this->getDoctrine()->getRepository(Companies::class)->find($id);

        return $this->render('default/company.html.twig', [
            'company' => $company,
            'vessels' => $this->getDoctrine()->getRepository(CompanyVessels::class)->findBy(['company' => $company])
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('default/about.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        return $this->render('default/contact.html.twig');
    }

    /**
     * @Route("/promos", name="promos")
     */
    public function promosAction(Request $request)
    {
        return $this->render('default/promos.html.twig');
    }

    /**
     * @Route("/sendemail", name="sendemail")
     */
    public function sendemailAction(Request $request)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($request->request->get('subject'))
            ->setFrom('noreply@shipsked.ph')
            ->setTo('admin@shipsked.ph')
            ->setBody($request->request->get('message'), 'text/html');

        $this->get('mailer')->send($message);

        $this->addFlash('success', 'Thank you for sending us a message. We will get back to you as soon as we finish reviewing it.');
        
        return $this->redirectToRoute("contact");
    }
}
