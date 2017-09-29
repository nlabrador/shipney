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

class ScrapeLite extends ContainerAwareCommand
{
    private $data = [];

    protected function configure()
    {
        $this->setName('scrape:sitelite')
             ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client  = new Client();
        $crawler = $client->request('GET', 'http://www.liteferries.com/schedules.html');

        $crawler->filter('.desc')->each(function ($node) {
            $sched_data = explode("<h3>", $node->html());
            array_shift($sched_data);

            foreach ($sched_data as $travel) {
                $scheds1 = explode("<p>", $travel);
                $scheds2 = explode("<br>", $scheds1[1]);

                foreach ($scheds2 as $time_sched) {
                    $ports = explode("to", $scheds1[0]);
                    $depart_port = strtoupper(trim(preg_replace('/City/', '', $ports[0])));
                    $depart_port = strtoupper(trim(preg_replace('/<BR>/', '', $depart_port)));
                    $depart_port = strtoupper(trim(preg_replace('/VIA.*$/', '', $depart_port)));
                    $depart_port = trim(preg_replace('/,.*$/', '', $depart_port)) . ' PORT';
                    $arrive_port = preg_replace('/City/', '', $ports[1]);
                    $arrive_port = preg_replace('/<br>/', '', $arrive_port);
                    $arrive_port = preg_replace('/via.*$/', '', $arrive_port);
                    $arrive_port = strtoupper(trim(preg_replace('/<.*/', '', $arrive_port)));
                    $arrive_port = trim(preg_replace('/,.*$/', '', $arrive_port)) . ' PORT';

                    $time = preg_replace('/PM .*$/', 'PM', trim($time_sched));
                    $time = trim(preg_replace('/AM .*$/', 'AM', $time));
                    $time = trim(preg_replace('/NN .*$/', 'PM', $time));
                    $time = trim(preg_replace('/MN .*$/', 'AM', $time));
                    $days = preg_replace('/^.*PM /', '', trim($time_sched));
                    $days = preg_replace('/^.*AM /', '', $days);
                    $days = preg_replace('/^.*NN /', '', $days);
                    $days = preg_replace('/^.*MN /', '', $days);

                    if ($time == '</p>') {
                        continue;
                    }
                    if (!$time) {
                        continue;
                    }
                
                    $depart_port = trim(preg_replace('/VIA.*/', 'PORT', $depart_port));
                    $arrive_port = trim(preg_replace('/VIA.*/', 'PORT', $arrive_port));

                    if ($depart_port == ' PORT') {
                        die(var_dump($ports[0]));
                        continue;
                    }
                    if ($arrive_port == ' PORT') {
                        die(var_dump($ports[1]));
                    }

                    $days = explode(",", $days);
                    $sched_day = [];
                    foreach ($days as $day) {
                        $day = preg_replace('/<.*$/', '', $day);
                        $sched_day[] = ucfirst(strtolower(trim($day)));
                    }

                    $depart_sched = implode('/', $sched_day);
                
                    $data = [
                        'vessel' => 'Lite Ferries',
                        'depart_time' => $time, 
                        'depart_sched' => $depart_sched, 
                        'arrive_time' => '', 
                        'company'   => 'Lite Shipping Corporation',
                        'address'   => '14 G.L Lavilles Street, Cor M.J Cuenco Tinago Cebu City',
                        'phone'     => '032 255-1721',
                        'website'   => 'http://www.liteferries.com',
                        'vessel_type' => 'Passenger',
                        'email'       => 'info@liteferries.com',
                        'depart_port' => $depart_port, 
                        'arrive_port' => $arrive_port
                    ];
        
                    $client  = new Client();
                    $crawler = $client->request('GET', 'http://www.liteferries.com/rates.html');
        
                    $accoms = $crawler->filter('.desc')->each(function ($node) {
                        $accom_data = explode("\n", trim($node->html()));

                        $ports = explode("to", $accom_data[0]);
                        $depart_port = strtoupper(trim(preg_replace('/City/', '', $ports[0])));
                        $depart_port = preg_replace('/<H3>/', '', $depart_port);
                        $depart_port = preg_replace('/<BR>/', '', $depart_port);
                        $depart_port = preg_replace('/VIA.*$/', '', $depart_port);
                        $depart_port = trim(preg_replace('/,.*$/', '', $depart_port)) . ' PORT';

                        $arrive_port = preg_replace('/City/', '', $ports[1]);
                        $arrive_port = preg_replace('/<br>/', '', $arrive_port);
                        $arrive_port = preg_replace('/via.*$/', '', $arrive_port);
                        $arrive_port = strtoupper(trim(preg_replace('/<.*/', '', $arrive_port)));
                        $arrive_port = trim(preg_replace('/,.*$/', '', $arrive_port)) . ' PORT';
                    
                        $depart_port = trim(preg_replace('/VIA.*/', ' PORT', $depart_port));
                        $arrive_port = trim(preg_replace('/VIA.*/', ' PORT', $arrive_port));

                        array_shift($accom_data);

                        $accom_prices = [];
                        foreach ($accom_data as $price) {
                            if (preg_match('/\d/', $price)) {
                                $acco = explode('-', $price);
                                $name = preg_replace('/<p>/', '', $acco[0]);
                                $name = trim(preg_replace('/-.*$/', '', $name));
                                $name_desc = explode(" ", $name);
                                $name = $name_desc[0];
                                $desc = isset($name_desc[1]) ? $name_desc[1] : '';

                                $price = array_pop($acco);
                                $price = preg_replace('/Php/', '', $price);
                                $price = preg_replace('/\(.*$/', '', $price);
                                $price = trim(preg_replace('/<.*$/', '', $price));
            
                                $accom_prices[$name] = [
                                    'price' => $price,
                                    'desc'  => $desc
                                ];
                            }
                        }

                        return [
                            $depart_port.'-'.$arrive_port => $accom_prices
                        ];
                    });

                    foreach ($accoms as $accom) {
                        $port_key = $depart_port."-".$arrive_port;
                        $port_key2 = $arrive_port."-".$depart_port;

                        if (isset($accom[$port_key])) {
                            $data['accoms'] = $accom;
                            break;
                        }
                        if (isset($accom[$port_key2])) {
                            $data['accoms'] = $accom;
                            break;
                        }
                    }

                    if (isset($data['accoms'])) {
                        $this->saveDb($data);
                    }
                }
            }
        });
    }
    
    public function saveDb($data) {
            $company = $this->getContainer()->get('doctrine')->getRepository(Companies::class)->findOneByName($data['company']);

            if (!$company) {
                $company = new Companies();
                $company->setName($data['company']);
            }

            $company->setAddress1($data['address']);
            $company->setPhone($data['phone']);
            $company->setWebsite($data['website'] ? $data['website'] : 'N/A');
            $company->setEmail($data['email'] ? $data['email'] : 'N/A');
            $company->setBookingUrl('https://liteshipping.barkota.com');

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($company);
            $em->flush();

            $depart_port = preg_replace('/VIA.*$/', 'PORT', $data['depart_port']);
            $data['depart_port'] = trim($depart_port);
            $arrive_port = preg_replace('/VIA.*$/', 'PORT', $data['arrive_port']);
            $data['arrive_port'] = trim($arrive_port);

            if ($data['depart_port'] == 'NASIPIT PORT') {
                $data['depart_port'] = 'BUTUAN PORT';
            }
            if ($data['arrive_port'] == 'NASIPIT PORT') {
                $data['arrive_port'] = 'BUTUAN PORT';
            }

            $dep_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                    'name' => $data['depart_port']
            ]);
            $arrive_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                    'name' => $data['arrive_port']
            ]);

            $depart_time = preg_replace('/\+/', '', $data['depart_time']);
            $depart_time = preg_replace('/DAILY/', '', $depart_time);
            $depart_time = trim(preg_replace('/<\/p>/', '', $depart_time));
            $data['depart_time'] = $depart_time;

            $vessel = $this->getContainer()->get('doctrine')->getRepository(CompanyVessels::class)->findOneBy([
                'company' => $company,
                'departPort' => $dep_port,
                'arrivePort' => $arrive_port,
                'name'      => $data['vessel'],
                'departTime' => $data['depart_time']
            ]);

            if (!$dep_port) {
                die(var_dump(['depart', $data['depart_port'], $data['arrive_port']]));
            }
            if (!$arrive_port) {
                die(var_dump(['arrive', $data['arrive_port'], $data['depart_port']]));
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

            foreach ($data['accoms'] as $accom => $accoms) {
                foreach ($accoms as $accom => $accom_data) {
                    $accomodation = $this->getContainer()->get('doctrine')->getRepository(VesselAccomodations::class)->findOneBy([
                            'accomodation'  => $accom,
                            'vessel' => $vessel
                    ]);

                    if (!$accomodation) {
                        $accomodation = new VesselAccomodations();
                        $accomodation->setVessel($vessel);
                        $accomodation->setAccomodation($accom);
                    }

                    $accomodation->setPrice($accom_data['price']);
                    $accomodation->setFeatures($accom_data['desc']);

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $em->persist($accomodation);
                    $em->flush();
                }
            }
        }
}
