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
        $companies = $this->getDoctrine()->getRepository(Companies::class)->findAll();

        return $this->render('default/index.html.twig', [
            'companies' => $companies
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function searchAction(Request $request)
    {
        $form = $this->createForm(FindSched::class);
        $form->handleRequest($request);

        $schedules = [];
        $from_form = false;

        $data = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $date = \DateTime::createFromFormat('F d, Y', $data['date']);

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery("
                SELECT
                    cv.id, c.id as com_id, cv.departTime, c.name as company, sp2.name as departPort,
                    cv.arriveTime, sp.name as arrivePort, cv.vesselType,
                    cv.passPriceRange, cv.vehiPriceRange, cv.name as vessel, dest.townCity as destCity,
                    dep.townCity as depCity, c.officesUrl, c.booksite, d.distance as depPortDistance, d.duration as depPortDuration,
                    (SELECT d2.distance FROM AppBundle:Distances d2 WHERE d2.seaPort = sp.id AND d2.targetTownCity = :destcity) as arrPortDistance,
                    (SELECT d3.duration FROM AppBundle:Distances d3 WHERE d3.seaPort = sp.id AND d3.targetTownCity = :destcity) as arrPortDuration
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
                ORDER by depPortDistance, arrPortDistance ASC, cv.departTime
            ");
            
            $query->setParameter('destcity', $data['destination']->getId());
            $query->setParameter('tcity', $data['origin']->getTownCity());
            $query->setParameter('dprovince', $data['destination']->getProvince());

            if (!$date) {
                $date = new \DateTime('now');
            }

            $query->setParameter('sday', '%'.$date->format('D').'%');

            $schedules = $query->getScalarResult();

            $from_form = true;
        }
        
        $companies = $this->getDoctrine()->getRepository(Companies::class)->findAll();

        return $this->render('default/search.html.twig', [
            'form'      => $form->createView(),
            'form2'      => $form->createView(),
            'schedules' => $schedules,
            'from_form' => $from_form,
            'origin'    => $data ? $data['origin']->getTownCity() : null,
            'destination' => $data ? $data['destination']->getTownCity() : null,
            'is_mobile' => $this->isMobile($request),
            'companies' => $companies
        ]);
    }

    public function isMobile(Request $request)
    {
        $useragent = $request->headers->get('User-Agent');
        if (!$useragent) {
            return false;
        }

        return (
                preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) ||
                preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent,0,4))
               );
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
                    cv.id, c.id as com_id, cv.departTime, c.name as company, sp2.name as departPort, c.phone,
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
            'booking_offices' => $offices,
            'companies' => $this->getDoctrine()->getRepository(Companies::class)->findAll()
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
            'vessels' => $this->getDoctrine()->getRepository(CompanyVessels::class)->findBy(['company' => $company]),
            'companies' => $this->getDoctrine()->getRepository(Companies::class)->findAll()
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('default/about.html.twig', ['companies' => $this->getDoctrine()->getRepository(Companies::class)->findAll()]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contactAction(Request $request)
    {
        return $this->render('default/contact.html.twig', ['companies' => $this->getDoctrine()->getRepository(Companies::class)->findAll()]);
    }

    /**
     * @Route("/promos", name="promos")
     */
    public function promosAction(Request $request)
    {
        return $this->render('default/promos.html.twig', ['companies' => $this->getDoctrine()->getRepository(Companies::class)->findAll()]);
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
