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

class ScrapeSupercat extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:sitesupercat')
             ;
    }

    public function getSchedules() {
        return [
            'CEBU-TAGBILARAN' => [
                'sched' => [
                    '5:50AM' => '7:50AM',
                    '8:15AM' => '10:15AM',
                    '11:00AM' => '12:00PM',
                    '1:15PM' => '3:15PM',
                    '2:30PM' => '4:30PM',
                    '3:35PM' => '5:35PM',
                    '6:00PM' => '8:00PM',
                    '7:15PM' => '9:15PM',
                ],
                'prices' => [
                    'Business' => '779.99',
                    'Tourist' => '540.00',
                    'Economy' => '440.00',
                ],
                'vessel' => 'St Sariel'
            ],
            'TAGBILARAN-CEBU' => [
                'sched' => [
                    '5:50AM' => '7:50AM',
                    '8:15AM' => '10:15AM',
                    '11:00AM' => '1:00PM',
                    '1:15PM' => '3:15PM',
                    '3:35PM' => '5:35PM',
                    '4:45PM' => '6:45PM',
                    '5:45PM' => '7:45PM',
                    '9:30PM' => '11:30PM',
                ],
                'prices' => [
                    'Business' => '779.99',
                    'Tourist' => '540.00',
                    'Economy' => '440.00',
                ],
                'vessel' => 'St Sariel',
            ],
            'CEBU-ORMOC' => [
                'sched' => [
                    '5:15AM' => '8:15AM',
                    '7:45AM' => '10:45AM',
                    '10:25AM' => '1:25PM',
                    '12:00PM' => '3:00PM',
                    '5:00PM' => '8:00PM',
                    '7:00PM' => '10:PM',
                ],
                'prices' => [
                    'Business' => '920.00',
                    'Tourist' => '720.00',
                ],
                'vessel' => 'St Jhudiel',
            ],
            'ORMOC-CEBU' => [
                'sched' => [
                    '7:15AM' => '10:15AM',
                    '8:30AM' => '11:30AM',
                    '11:15AM' => '2:15PM',
                    '1:45PM' => '4:45PM',
                    '3:45PM' => '6:45PM',
                    '8:10PM' => '11:10PM',
                ],
                'prices' => [
                    'Business' => '920.00',
                    'Tourist' => '720.00',
                ],
                'vessel' => 'St Jhudiel',
                'travel_hour' => '3'
            ],
            'BATANGAS-CALAPAN' => [
                'sched' => [
                    '6:00AM' => '7:00AM',
                    '7:30AM' => '8:30AM',
                    '9:00AM' => '10:00AM',
                    '10:30AM' => '11:30AM',
                    '12:30PM' => '1:30PM',
                    '2:00PM' => '3:00PM',
                    '3:30PM' => '4:30PM',
                    '5:00PM' => '6:00PM',
                    '6:30PM' => '7:30PM',
                    '8:00PM' => '8:30PM',
                ],
                'prices' => [
                    'Business' => '355.00',
                    'Economy Aircon' => '235.00',
                    'Economy' => '235.00',
                    'Tourist' => '324.99'
                ],
                'vessel' => 'St Nuriel'
            ],
            'CALAPAN-BATANGAS' => [
                'sched' => [
                    '4:45AM' => '4:45AM',
                    '6:00AM' => '6:00AM',
                    '7:30AM' => '7:30AM',
                    '9:00AM' => '9:00AM',
                    '10:30AM' => '10:30AM',
                    '12:30PM' => '12:30PM',
                    '2:00PM' => '2:00PM',
                    '3:30PM' => '3:30PM',
                    '5:00PM' => '5:00PM',
                    '6:30PM' => '6:30PM',
                ],
                'prices' => [
                    'Business' => '355.00',
                    'Economy Aircon' => '235.00',
                    'Economy' => '235.00',
                    'Tourist' => '324.99'
                ],
                'vessel' => 'St Nuriel'
            ],
            'BACOLOD-ILOILO' => [
                'sched' => [
                    '6:00AM' => '7:00AM',
                    '9:00AM' => '10:00AM',
                    '12:20PM' => '1:20PM',
                    '3:30PM' => '4:30PM'
                ],
                'prices' => [
                    'Business' => '435',
                    'Economy' => '229.99',
                    'Tourist' => '320.00'
                ],
                'vessel' => 'St Sealthiel'
            ],
            'ILOILO-BACOLOD' => [
                'sched' => [
                    '7:30AM' => '8:30AM',
                    '10:30AM' => '11:30AM',
                    '2:00PM' => '3:00PM',
                    '5:00PM' => '6:00PM'
                ],
                'prices' => [
                    'Business' => '435',
                    'Economy' => '229.99',
                    'Tourist' => '320.00'
                ],
                'vessel' => 'St Sealthiel'
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
                        'company'   => '2GO Group Inc.',
                        'address'   => 'Paranaque, Manila',
                        'phone'     => '02 528 7000',
                        'website'   => 'travel.2go.com.ph',
                        'vessel_type' => 'Fastcraft',
                        'email'       => 'info@2go.com.ph',
                        'booksite' => 'http://travel.2go.com.ph/eTicket/search.asp',
                        'offices_url' => 'http://travel.2go.com.ph/Outlets/locator.asp',
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
