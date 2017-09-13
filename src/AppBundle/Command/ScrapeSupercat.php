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

use Goutte\Client;

class ScrapeSupercat extends ContainerAwareCommand
{
    protected function configure()
    {
        //TODO make same with 2go pass parameters origin destination date accomodation
        $this->setName('scrape:sitesupercat')
             ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schedules = [
            'CEBU-TAGBILARAN' => [
                '5:50AM'=> true,
                '8:15AM'=> true,
                '11:00AM'=> true,
                '1:15PM'=> true,
                '2:30PM'=> true,
                '3:35PM'=> true,
                '6:00PM'=> true,
                '7:15PM'=> true
            ],
            'TAGBILARAN-CEBU' => [
                '5:50AM'=> true,
                '8:15AM'=> true,
                '11:00AM'=> true,
                '1:15PM'=> true,
                '3:35PM'=> true,
                '4:45PM'=> true,
                '5:45PM'=> true,
                '9:30PM'=> true,
            ],
            'CEBU-ORMOC' => [
                '5:15AM'=> true,
                '7:45AM'=> true,
                '10:25AM'=> true,
                '12:00PM'=> true,
                '5:00PM'=> true,
                '7:00PM'=> true,
            ],
            'ORMOC-CEBU' => [
                '7:15AM'=> true,
                '8:30AM'=> true,
                '11:15AM'=> true,
                '1:45PM'=> true,
                '3:45PM'=> true,
                '8:10PM'=> true,
            ],
            'BACOLOD-ILOILO' => [
                '6:00AM'=> true,
                '9:00AM'=> true,
                '12:20PM'=> true,
                '3:30PM'=> true,
            ],
            'ILOILO-BACOLOD' => [
                '7:30AM'=> true,
                '10:30AM'=> true,
                '2:00PM'=> true,
                '5:00PM'=> true,
            ],
            'CALAPAN-BATANGAS' => [
                '4:45AM'=> true,
                '6:00AM'=> true,
                '7:30AM'=> true,
                '9:00AM'=> true,
                '10:30AM'=> true,
                '12:30PM'=> true,
                '2:00PM'=> true,
                '3:30PM'=> true,
                '5:00PM'=> true,
                '6:30PM'=> true,
            ],
            'BATANGAS-CALAPAN' => [
                '6:00 AM'=> true,
                '7:30 AM'=> true,
                '9:00 AM'=> true,
                '10:30 AM'=> true,
                '12:30 PM'=> true,
                '2:00 PM'=> true,
                '3:30 PM'=> true,
                '5:00 PM'=> true,
                '6:30 PM'=> true,
                '8:00 PM'=> true,
            ]
        ];

        foreach ($schedules as $schedule) {
        }

        $client  = new Client();
        $crawler = $client->request('GET', 'http://travel.2go.com.ph/Schedules/schedules.asp');

        $form = $crawler->selectButton('imageField')->form();
        $form->disableValidation();
        $form['orig']->select('BTS');
        $crawler = $client->submit($form, array('orig' => $input->getArgument('origin'), 'dest' => $input->getArgument('destination'), 'sched' => $input->getArgument('date')));

        $crawler->filter('.table-schedules')->each(function ($node) {
            $node->filter('tr')->each(function ($node) {
                if (preg_match('/Open Voyages from/', $node->text())) {
                    $voy = explode("to", preg_replace("/Open Voyages from /", "", $node->text()));
                    $this->depart_port_name = preg_replace("/CITY|CITY OF|JETTY|PORT|, NASIPIT|, PALAWAN/", "", $voy[0]);
                    $this->arrive_port_name = preg_replace("/CITY|CITY OF|JETTY|PORT|, NASIPIT|, PALAWAN/", "", $voy[1]);
                }

                if (!preg_match('/Departure|Open Voyages|Time/', $node->text())) {
                    $fields = explode("\n", $node->text());
                    $vessel_names = array_map(function ($name) { return ucfirst(strtolower($name)); }, explode(" ", trim($fields[0])));

                    if (preg_match('/,/', $fields[2])) {
                        $fields[2] = $fields[3];
                        $fields[3] = $fields[4];
                        $fields[6] = $fields[7];
                    }

                    $data = [
                        'vessel' => implode(" ", $vessel_names),
                        'depart_time' => preg_replace("/^0/", "", trim($fields[2])),
                        'depart_sched' => trim($fields[3]),
                        'arrive_time' => preg_replace("/^0/", "", trim($fields[6])),
                        'company'   => '2GO Group Inc.',
                        'address'   => 'Paranaque, Manila',
                        'phone'     => '02 528 7000',
                        'website'   => 'travel.2go.com.ph',
                        'vessel_type' => 'Passenger',
                        'email'       => 'info@2go.com.ph',
                        'depart_port' => strtoupper(trim($this->depart_port_name) . ' PORT'),
                        'arrive_port' => strtoupper(trim($this->arrive_port_name) . ' PORT')
                    ];

                    if ($data['depart_port'] == 'OZAMIZ PORT') {
                        $data['depart_port'] = 'OZAMIS PORT';
                    }
                    if ($data['arrive_port'] == 'OZAMIZ PORT') {
                        $data['arrive_port'] = 'OZAMIS PORT';
                    }
                    if ($data['depart_port'] == 'DIPOLOG PORT') {
                        $data['depart_port'] = 'DAPITAN PORT';
                    }
                    if ($data['arrive_port'] == 'DIPOLOG PORT') {
                        $data['arrive_port'] = 'DAPITAN PORT';
                    }
                    
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

                    $sched = ucfirst(strtolower($data['depart_sched']));
                    if ($vessel) {
                        if (!preg_match("/$sched/", $vessel->getSchedDay())) {
                            $sched = $vessel->getSchedDay()."/".$sched;

                            $vessel->setSchedDay($sched);
                        }
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
                }
            });
        });
    }
}
