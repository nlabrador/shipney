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

class ScrapeWeesam extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:siteweesam')
             ;
    }

    public function getSchedules() {
        return [
            'CEBU-TAGBILARAN' => [
                'sched' => [
                    '9:00AM' => '10:00AM',
                    '2:00PM' => '3:00PM',
                    '6:30PM' => '7:30PM',
                ],
                'prices' => [
                    'First Class' => '600.00',
                    'Economy Aircon' => '500.00',
                    'Economy Non-Aircon' => '400.00',
                ],
                'vessel' => 'Weesam Express'
            ],
            'TAGBILARAN-CEBU' => [
                'sched' => [
                    '7:00AM' => '8:00AM',
                    '11:20AM' => '12:20PM',
                    '4:30PM' => '5:30PM',
                ],
                'prices' => [
                    'First Class' => '600.00',
                    'Economy Aircon' => '500.00',
                    'Economy Non-Aircon' => '400.00',
                ],
                'vessel' => 'Weesam Express',
            ],
            'CEBU-ORMOC' => [
                'sched' => [
                    '10:45AM' => '1:45PM',
                    '4:20AM' => '7:20AM',
                ],
                'prices' => [
                    'First Class' => '700.00',
                    'Economy Aircon' => '650.00',
                    'Economy Non-Aircon' => '550.00',
                ],
                'vessel' => 'Weesam Express',
            ],
            'ORMOC-CEBU' => [
                'sched' => [
                    '8:00AM' => '11:300AM',
                    '1:30PM' => '4:30PM',
                ],
                'prices' => [
                    'First Class' => '700.00',
                    'Economy Aircon' => '650.00',
                    'Economy Non-Aircon' => '550.00',
                ],
                'vessel' => 'Weesam Express',
            ],
            'BACOLOD-ILOILO' => [
                'sched' => [
                    '6:30AM' => '',
                    '8:10AM' => '',
                    '9:50AM' => '',
                    '11:30AM' => '',
                    '2:50PM' => '',
                    '4:45PM' => '',
                ],
                'prices' => [
                    'First Class' => '425.00',
                    'Economy' => '330.00',
                ],
                'vessel' => 'Weesam Express'
            ],
            'ILOILO-BACOLOD' => [
                'sched' => [
                    '6:30AM' => '',
                    '8:10AM' => '',
                    '9:50AM' => '',
                    '11:10AM' => '',
                    '2:50PM' => '',
                    '4:45PM' => '',
                ],
                'prices' => [
                    'First Class' => '425.00',
                    'Economy' => '330.00',
                ],
                'vessel' => 'Weesam Express'
            ],
            'ZAMBOANGA-ISABELA' => [
                'sched' => [
                    '6:45AM' => '',
                    '9:30AM' => '',
                    '12:45PM' => '',
                    '3:30PM' => '',
                ],
                'prices' => [
                    'First Class' => '190.00',
                    'Economy' => '150.00',
                ],
                'vessel' => 'Weesam Express'
            ],
            'ISABELA-ZAMBOANGA' => [
                'sched' => [
                    '8:15AM' => '',
                    '10:45PM' => '',
                    '2:00PM' => '',
                    '4:45PM' => '',
                ],
                'prices' => [
                    'First Class' => '190.00',
                    'Economy' => '150.00',
                ],
                'vessel' => 'Weesam Express'
            ],
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getSchedules() as $route => $sched) {
                $ports = explode("-", $route);
                $depart_port_name = $ports[0] . " PORT";
                $arrival_port_name = $ports[1] . " PORT";

                foreach ($sched['sched'] as $depart_time => $arrival_time) {
                    $data = [
                        'vessel' => $sched['vessel'], 
                        'depart_time' => $depart_time,
                        'depart_sched' => 'Daily',
                        'arrive_time' => $arrival_time, 
                        'company'   => 'WeeSam Express',
                        'address'   => 'Pier 4, North Reclamation Area, Cebu City',
                        'phone'     => '032 412-9562',
                        'website'   => 'http://www.weesamexpress.net/',
                        'vessel_type' => 'Fastcraft',
                        'email'       => 'n/a',
                        'booksite'    => '',
                        'depart_port' => $depart_port_name, 
                        'arrive_port' => $arrival_port_name
                    ];

                    $company = $this->getContainer()->get('doctrine')->getRepository(Companies::class)->findOneByName($data['company']);

                    if (!$company) {
                        $company = new Companies();
                        $company->setName($data['company']);
                    }

                    $company->setAddress1($data['address']);
                    $company->setPhone($data['phone']);
                    $company->setWebsite($data['website'] ? $data['website'] : 'N/A');
                    $company->setEmail($data['email'] ? $data['email'] : 'N/A');

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

                    if ($vessel) {
                        $vessel->setSchedDay($data['depart_sched']);
                    }
                    else {
                        $vessel = new CompanyVessels();
                        $vessel->setCompany($company);
                        $vessel->setDepartPort($dep_port);
                        $vessel->setArrivePort($arrive_port);
                        $vessel->setName($data['vessel']);
                        $vessel->setDepartTime($data['depart_time']);
                        $vessel->setArriveTime($data['arrive_time']);
                        $vessel->setSchedDay($data['depart_sched']);
                        $vessel->setVesselType($data['vessel_type']);
                    }

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $em->persist($vessel);
                    $em->flush();

                    foreach ($sched['prices'] as $accom => $price) {
                        $accomodation = $this->getContainer()->get('doctrine')->getRepository(VesselAccomodations::class)->findOneBy([
                            'accomodation'  => $accom,
                            'vessel' => $vessel
                        ]);

                        if (!$accomodation) {
                            $accomodation = new VesselAccomodations();
                            $accomodation->setVessel($vessel);
                            $accomodation->setAccomodation($accom);
                        }

                        $accomodation->setPrice($price);
                        $accomodation->setFeatures('');

                        $em = $this->getContainer()->get('doctrine')->getManager();
                        $em->persist($accomodation);
                        $em->flush();
                    }
                }
        }
    }
}
