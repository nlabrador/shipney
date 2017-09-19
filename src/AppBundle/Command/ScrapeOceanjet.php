<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\TownCities;
use AppBundle\Entity\Companies;
use AppBundle\Entity\CompanyVessels;
use AppBundle\Entity\SeaPorts;
use AppBundle\Entity\VesselAccomodations;

use Goutte\Client;

class ScrapeOceanjet extends ContainerAwareCommand
{
    private $data = [];

    protected function configure()
    {
        $this->setName('scrape:siteoceanjet')
             ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client  = new Client();
        $crawler = $client->request('GET', 'http://www.oceanjet.net/fare-schedule');

        $crawler->filter('.tbl-fare')->each(function ($node) {
                $data = [
                    'vessel' => 'Oceanjet',
                    'depart_time' => '', 
                    'depart_sched' => 'Daily', 
                    'arrive_time' => '', 
                    'company'   => 'Ocean Fast Ferries, Inc.',
                    'address'   => 'Pier 1, Cebu Pier Area, Cebu City',
                    'phone'     => '032-255-7560',
                    'website'   => 'http://www.oceanjet.net',
                    'vessel_type' => 'Fastcfraft',
                    'email'       => 'customerservice@oceanjet.net',
                    'depart_port' => '', 
                    'arrive_port' => ''
                ];

                $sched = explode("\n", trim($node->filter('.fare-box')->first()->text()));
                $depart = trim($sched[0]);
                $arrival = trim($sched[2]);

                $depart_time = preg_replace("/AM .*$/", "AM", $depart);
                $depart_time = preg_replace("/PM .*$/", "PM", $depart_time);
                $depart_port_name = preg_replace("/^.*AM /", "", $depart);
                $depart_port_name = preg_replace("/^.*PM /", "", $depart_port_name);

                $arrival_time = preg_replace("/AM .*$/", "AM", $arrival);
                $arrival_time = preg_replace("/PM .*$/", "PM", $arrival_time);
                $arrival_port_name = preg_replace("/^.*AM /", "", $arrival);
                $arrival_port_name = preg_replace("/^.*PM /", "", $arrival_port_name);

                if (preg_match("/^LARENA|SIQUIJOR,/", $depart_port_name)) {
                    $depart_port_name = 'SIQUIJOR';
                }
                if (preg_match("/^LARENA|SIQUIJOR,/", $arrival_port_name)) {
                    $arrival_port_name = 'SIQUIJOR';
                }
                if (preg_match("/CAMOTES/", $depart_port_name)) {
                    $depart_port_name = 'PORO';
                }
                if (preg_match("/CAMOTES/", $arrival_port_name)) {
                    $arrival_port_name = 'PORO';
                }

                $data['depart_time'] = trim($depart_time);
                $data['depart_port'] = trim($depart_port_name) . " PORT";
                $data['arrive_time'] = trim($arrival_time);
                $data['arrive_port'] = trim($arrival_port_name) . " PORT";

                $accoms = $node->filter('.fare-box')->last()->filter('tr')->each(function ($node) {

                    $accom_data = explode("\n", trim($node->text()));

                    $price = trim(preg_replace("/PHP/", "", $accom_data[1]));
                    if ($price == '-----') {
                        $price = 0;
                    }
                    return [
                        'accomodation' => trim($accom_data[0]),
                        'price'        => $price 
                    ];
                });

                $data['accomodations'] = $accoms;

                $this->data[] = $data;
        });

        foreach ($this->data as $data) {
            $company = $this->getContainer()->get('doctrine')->getRepository(Companies::class)->findOneByName($data['company']);

            if (!$company) {
                $company = new Companies();
                $company->setName($data['company']);
            }

            $company->setAddress1($data['address']);
            $company->setPhone($data['phone']);
            $company->setWebsite($data['website'] ? $data['website'] : 'N/A');
            $company->setEmail($data['email'] ? $data['email'] : 'N/A');
            $company->setOfficesUrl('http://www.oceanjet.net/ticket-outlets');
            $company->setPromoUrl('http://www.oceanjet.net/ticket-promos');

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($company);
            $em->flush();

            $dep_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                    'name' => $data['depart_port']
            ]);
            $arrive_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                    'name' => $data['arrive_port']
            ]);

            $vessel = $this->getContainer()->get('doctrine')->getRepository(CompanyVessels::class)->findOneBy([
                'company' => $company,
                'departPort' => $dep_port,
                'arrivePort' => $arrive_port,
                'name'      => $data['vessel'],
                'departTime' => $data['depart_time']
            ]);

            if (!$dep_port) {
                die(var_dump($data['depart_port']));
            }
            if (!$arrive_port) {
                die(var_dump($data['arrive_port']));
            }

            $sched = $data['depart_sched'];
            if ($vessel) {
                $vessel->setSchedDay($sched);
            }
            else {
                $vessel = new CompanyVessels();
                $vessel->setCompany($company);
                $vessel->setDepartPort($dep_port);
                $vessel->setArrivePort($arrive_port);
                $vessel->setName($data['vessel']);
                $vessel->setDepartTime($data['depart_time']);
                $vessel->setArriveTime($data['arrive_time']);
                $vessel->setSchedDay($sched);
                $vessel->setVesselType($data['vessel_type']);
            }

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($vessel);
            $em->flush();

            foreach ($data['accomodations'] as $accom_data) {
                $accomodation = $this->getContainer()->get('doctrine')->getRepository(VesselAccomodations::class)->findOneBy([
                    'accomodation'  => $accom_data['accomodation'],
                    'vessel' => $vessel
                ]);

                if (!$accomodation) {
                    $accomodation = new VesselAccomodations();
                    $accomodation->setVessel($vessel);
                    $accomodation->setAccomodation($accom_data['accomodation']);
                }

                $accomodation->setPrice($accom_data['price']);
                $accomodation->setFeatures('');

                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($accomodation);
                $em->flush();
            }
        }
    }
}
