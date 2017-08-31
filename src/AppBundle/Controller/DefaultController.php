<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\FindSched;

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
                    cv.id, cv.departTime, c.name as company, sp2.name as departPort,
                    cv.arriveTime, sp.name as arrivePort, cv.vesselType,
                    cv.passPriceRange, cv.vehiPriceRange
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
                    AND cv.schedDay LIKE :sday
            "); 
            
            $query->setParameter('tcity', $data['origin']->getTownCity());
            $query->setParameter('dprovince', $data['destination']->getProvince());
            $query->setParameter('sday', '%'.$date->format('D').'%');

            $schedules = $query->getScalarResult();
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
                    cv.id, cv.departTime, c.name as company, sp2.name as departPort,
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
     * @Route("/company", name="company")
     */
    public function companyAction(Request $request)
    {
        return $this->render('default/company.html.twig', [
        ]);
    }
}
