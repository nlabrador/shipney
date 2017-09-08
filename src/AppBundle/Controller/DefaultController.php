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
                    cv.passPriceRange, cv.vehiPriceRange, cv.name as vessel, dest.townCity as destCity
                FROM AppBundle:CompanyVessels cv
                    JOIN AppBundle:Companies c WITH c.id = cv.company
                    JOIN AppBundle:SeaPorts sp WITH sp.id = cv.arrivePort
                    JOIN AppBundle:SeaPorts sp2 WITH sp2.id = cv.departPort
                    JOIN AppBundle:TownCities dest WITH dest.id = sp.townCity
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

    /**
     * @Route("/detail/{id}", name="detail")
     */
    public function detailAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("
                SELECT
                    cv.id, c.id as com_id, cv.departTime, c.name as company, sp2.name as departPort,
                    cv.arriveTime, sp.name as arrivePort, cv.vesselType,
                    cv.passPriceRange, cv.vehiPriceRange, cv.name as vessel
                FROM AppBundle:CompanyVessels cv
                    JOIN AppBundle:Companies c WITH c.id = cv.company
                    JOIN AppBundle:SeaPorts sp WITH sp.id = cv.arrivePort
                    JOIN AppBundle:SeaPorts sp2 WITH sp2.id = cv.departPort
                    JOIN AppBundle:TownCities dest WITH dest.id = sp.townCity
                    JOIN AppBundle:Distances d WITH cv.departPort = d.seaPort
                    JOIN AppBundle:TownCities tc2 WITH tc2.id = d.targetTownCity
                WHERE
                    cv.id = :id
        "); 
        $query->setParameter('id', $id);
        $schedule = $query->getScalarResult()[0];

        $query = $em->createQuery("
            SELECT va.accomodation, va.price, va.features
            FROM AppBundle:VesselAccomodations va
            WHERE va.vessel = :id
        ");
        $query->setParameter('id', $id);
        $accomodations = $query->getScalarResult();

        return $this->render('default/detail.html.twig', [
            'schedule'      => $schedule,
            'accomodations' => $accomodations
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
