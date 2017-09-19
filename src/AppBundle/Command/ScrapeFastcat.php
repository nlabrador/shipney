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

class ScrapeFastcat extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:sitefastcat')
             ;
    }

    public function getSchedules() {
        return [
            'DUMAGUETE-DAPITAN' => [
                'sched' => [
                    '6:00AM' => '9:00AM',
                    '2:00PM' => '5:00PM',
                    '10:00PM' => '1:00AM',
                ],
                'prices' => [
                    'Business' => '500.00',
                    'Premium' => '440.00',
                    'Economy' => '380.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'DAPITAN-DUMAGUETE' => [
                'sched' => [
                    '10:00AM' => '1:00PM',
                    '6:00PM' => '9:00PM',
                    '2:00AM' => '5:00AM',
                ],
                'prices' => [
                    'Business' => '500.00',
                    'Premium' => '440.00',
                    'Economy' => '380.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'BULALACAO-CATICLAN' => [
                'sched' => [
                    '10:00AM' => '1:00PM',
                    '10:00PM' => '1:00AM',
                ],
                'prices' => [
                    'Business' => '500.00',
                    'Premium' => '440.00',
                    'Economy' => '375.00',
                ],
                'vessel' => 'Fastcat',
            ],
            'CATICLAN-BULALACAO' => [
                'sched' => [
                    '3:00AM' => '6:00AM',
                    '3:00PM' => '6:00PM',
                ],
                'prices' => [
                    'Business' => '500.00',
                    'Premium' => '440.00',
                    'Economy' => '375.00',
                ],
                'vessel' => 'Fastcat',
            ],
            'BATANGAS-CALAPAN' => [
                'sched' => [
                    '12:00AM' => '1:30AM',
                    '1:30AM' => '3:00AM',
                    '6:00AM' => '7:30AM',
                    '9:00AM' => '10:30AM',
                    '11:00AM' => '12:30PM',
                    '2:30PM' => '4:00PM',
                    '6:00PM' => '7:30PM',
                    '8:00PM' => '9:30',
                ],
                'prices' => [
                    'Business' => '300.00',
                    'Premium' => '250.00',
                    'Economy' => '190.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'CALAPAN-BATANGAS' => [
                'sched' => [
                    '2:30AM' => '4:00AM',
                    '6:30AM' => '8:00AM',
                    '8:30AM' => '10:00AM',
                    '12:00PM' => '1:30PM',
                    '2:00PM' => '3:30PM',
                    '5:00PM' => '6:30PM',
                    '9:00PM' => '10:30PM',
                    '11:00PM' => '12:30AM',
                ],
                'prices' => [
                    'Business' => '300.00',
                    'Premium' => '250.00',
                    'Economy' => '190.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'BACOLOD-ILOILO' => [
                'sched' => [
                    '5:00AM' => '6:40AM',
                    '11:00AM' => '12:40PM',
                    '5:00PM' => '6:40PM',
                    '11:00PM' => '12:40AM'
                ],
                'prices' => [
                    'Business' => '300.00',
                    'Premium' => '250.00',
                    'Economy' => '200.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'ILOILO-BACOLOD' => [
                'sched' => [
                    '8:00AM' => '9:40AM',
                    '2:00PM' => '3:40PM',
                    '8:00PM' => '9:40PM',
                    '2:00AM' => '3:40AM'
                ],
                'prices' => [
                    'Business' => '300.00',
                    'Premium' => '250.00',
                    'Economy' => '200.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'SAN CARLOS-TOLEDO' => [
                'sched' => [
                    '1:00AM' => '2:00AM',
                    '5:00AM' => '6:00AM',
                    '9:00AM' => '10:00AM',
                    '1:00PM' => '2:00PM',
                    '5:00PM' => '6:00PM'
                ],
                'prices' => [
                    'Business' => '250.00',
                    'Premium' => '200.00',
                    'Economy' => '150.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'TOLEDO-SAN CARLOS' => [
                'sched' => [
                    '3:00AM' => '4:00AM',
                    '7:00AM' => '8:00AM',
                    '11:00AM' => '12:00PM',
                    '3:00PM' => '4:00PM',
                    '7:00PM' => '8:00PM'
                ],
                'prices' => [
                    'Business' => '250.00',
                    'Premium' => '200.00',
                    'Economy' => '150.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'CEBU-TUBIGON' => [
                'sched' => [
                    '1:30AM' => '3:00AM',
                    '7:30AM' => '9:00AM',
                    '1:30PM' => '3:00PM',
                    '8:00PM' => '9:30PM'
                ],
                'prices' => [
                    'Business' => '300.00',
                    'Premium' => '270.00',
                    'Economy' => '240.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'TUBIGON-CEBU' => [
                'sched' => [
                    '5:00AM' => '6:30AM',
                    '10:30AM' => '12:00PM',
                    '5:30PM' => '7:00PM',
                    '10:30PM' => '12:00AM'
                ],
                'prices' => [
                    'Business' => '300.00',
                    'Premium' => '270.00',
                    'Economy' => '240.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'MATNOG-SAN ISIDRO' => [
                'sched' => [
                    '6:00AM' => '7:30AM',
                    '12:00PM' => '1:30PM',
                    '6:00PM' => '7:30PM',
                    '12:00AM' => '1:30AM'
                ],
                'prices' => [
                    'Business' => '224.00',
                    'Premium' => '184.00',
                    'Economy' => '144.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'SAN ISIDRO-MATNOG' => [
                'sched' => [
                    '3:00AM' => '7:30AM',
                    '9:00AM' => '10:30AM',
                    '3:00PM' => '4:30PM',
                    '9:00PM' => '10:30PM'
                ],
                'prices' => [
                    'Business' => '224.00',
                    'Premium' => '184.00',
                    'Economy' => '144.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'LILOAN-LIPATA' => [
                'sched' => [
                    '4:00AM' => '6:00AM',
                    '12:00PM' => '2:00PM',
                    '8:00PM' => '10:00PM'
                ],
                'prices' => [
                    'Business' => '360.00',
                    'Premium' => '330.00',
                    'Economy' => '300.00',
                ],
                'vessel' => 'Fastcat'
            ],
            'LIPATA-LILOAN' => [
                'sched' => [
                    '12:00AM' => '2:00PM',
                    '8:00AM' => '10:00AM',
                    '4:00PM' => '6:00PM'
                ],
                'prices' => [
                    'Business' => '360.00',
                    'Premium' => '330.00',
                    'Economy' => '300.00',
                ],
                'vessel' => 'Fastcat'
            ],
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getSchedules() as $route => $sched) {
                $ports = explode("-", $route);
                $depart_port_name = $ports[0] . " PORT";
                $arrival_port_name = $ports[1] . " PORT";

                if ($depart_port_name == 'LIPATA PORT') {
                    $depart_port_name = 'SURIGAO PORT';
                }
                if ($arrival_port_name == 'LIPATA PORT') {
                    $arrival_port_name = 'SURIGAO PORT';
                }

                foreach ($sched['sched'] as $depart_time => $arrival_time) {
                    $data = [
                        'vessel' => $sched['vessel'], 
                        'depart_time' => $depart_time,
                        'depart_sched' => 'Daily',
                        'arrive_time' => $arrival_time, 
                        'company'   => 'Archipelago Philippine Ferries Corp.',
                        'address'   => 'Commerce Avenue, Ayala Alabang, Muntinlupa City',
                        'phone'     => '02-842-9341',
                        'website'   => 'http://fastcat.com.ph',
                        'vessel_type' => 'Fastcraft/RORO',
                        'email'       => 'customercare.apfc@fastcat.com.ph',
                        'booksite' => 'http://fastcat.com.ph/archipelago-book-now.html',
                        'offices_url' => 'http://fastcat.com.ph/archipelago-contact.html',
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
                    $company->setBooksite($data['booksite']);
                    $company->setOfficesUrl($data['offices_url']);

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $em->persist($company);
                    $em->flush();

                    $dep_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                        'name' => $data['depart_port']
                    ]);
                    $arrive_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                        'name' => $data['arrive_port']
                    ]);

                    if (!$dep_port) {
                        die(var_dump($data['depart_port']));
                    }
                    if (!$arrive_port) {
                        die(var_dump($data['arrive_port']));
                    }

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
