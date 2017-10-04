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

class ScrapeTransAsia extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:sitetransasia')
             ;
    }

    public function getSchedules() {
        return [
            'CEBU-CAGAYAN DE ORO' => [
                'day' => 'Daily',
                'sched' => [
                    '8:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '970',
                    'Aircon' => '1000',
                    'Tourist' => '1095',
                    'Tourist Deluxe' => '1255',
                    'Cabin' => '1395',
                    'Private Room' => '3500',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'CAGAYAN DE ORO-CEBU' => [
                'day' => 'Daily',
                'sched' => [
                    '8:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '970',
                    'Aircon' => '1000',
                    'Tourist' => '1095',
                    'Tourist Deluxe' => '1255',
                    'Cabin' => '1395',
                    'Private Room' => '3500',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'CEBU-OZAMIS' => [
                'day' => 'Mon/Wed/Fri',
                'sched' => [
                    '8:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '910',
                    'Aircon' => '940',
                    'Tourist' => '1085',
                    'Tourist Deluxe' => '1160',
                    'Cabin' => '1390',
                    'Private Room' => '3180',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'OZAMIS-CEBU' => [
                'day' => 'Tue/Thu/Sun',
                'sched' => [
                    '8:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '910',
                    'Aircon' => '940',
                    'Tourist' => '1085',
                    'Tourist Deluxe' => '1160',
                    'Cabin' => '1390',
                    'Private Room' => '3180',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'CEBU-ILOILO' => [
                'day' => 'Mon/Wed/Fri',
                'sched' => [
                    '6:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '800',
                    'Tourist' => '885',
                    'Cabin' => '1015',
                    'Private Room' => '2420',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'ILOILO-CEBU' => [
                'day' => 'Tue/Thu/Sat',
                'sched' => [
                    '6:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '800',
                    'Tourist' => '885',
                    'Cabin' => '1015',
                    'Private Room' => '2420',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'CEBU-TAGBILARAN' => [
                'day' => 'Mon',
                'sched' => [
                    '12:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '195',
                    'Tourist' => '264',
                    'Cabin' => '375',
                    'Private Room' => '475',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'TAGBILARAN-CEBU' => [
                'day' => 'Sun',
                'sched' => [
                    '10:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '195',
                    'Tourist' => '264',
                    'Cabin' => '375',
                    'Private Room' => '475',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'CAGAYAN DE ORO-TAGBILARAN' => [
                'day' => 'Tue/Thu/Sat',
                'sched' => [
                    '7:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '660',
                    'Aircon' => '690',
                    'Tourist' => '775',
                    'Tourist Deluxe' => '870',
                    'Cabin' => '965',
                    'Private Room' => '1090',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'TAGBILARAN-CAGAYAN DE ORO' => [
                'day' => 'Mon/Wed/Fri',
                'sched' => [
                    '7:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '660',
                    'Aircon' => '690',
                    'Tourist' => '775',
                    'Tourist Deluxe' => '870',
                    'Cabin' => '965',
                    'Private Room' => '1090',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'CEBU-ILIGAN' => [
                'day' => 'Wed',
                'sched' => [
                    '7:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '910',
                    'Aircon' => '940',
                    'Tourist' => '1090',
                    'Tourist Deluxe' => '1220',
                    'Cabin' => '1390',
                    'Private Room' => '3320',
                ],
                'vessel' => 'Trans-Asia Shipping'
            ],
            'ILIGAN-CEBU' => [
                'day' => 'Thu',
                'sched' => [
                    '7:00PM' => '',
                ],
                'prices' => [
                    'Non Aircon' => '910',
                    'Aircon' => '940',
                    'Tourist' => '1090',
                    'Tourist Deluxe' => '1220',
                    'Cabin' => '1390',
                    'Private Room' => '3320',
                ],
                'vessel' => 'Trans-Asia Shipping'
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
                        'depart_sched' => $sched['day'],
                        'arrive_time' => $arrival_time, 
                        'company'   => 'Trans-Asia Shipping Lines, Inc',
                        'address'   => 'Cor. MJ Cuenco Ave., & Osmeña Blvd., Cebu City',
                        'phone'     => '032 254-6491',
                        'website'   => 'http://www.transasiashipping.com',
                        'vessel_type' => 'Passenger',
                        'email'       => 'infor@transasiashipping.com',
                        'booksite' => 'http://www.transasiashipping.com/e-ticket.html',
                        'offices_url' => 'http://www.transasiashipping.com/cebu-outlets.html',
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
