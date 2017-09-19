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

class ScrapeCokaliong extends ContainerAwareCommand
{
    private $data = [];
    private $depart_port_name;
    private $arrival_port_name;
    private $route;
    private $vessel;

    protected function configure()
    {
        $this->setName('scrape:sitecokaliong')
             ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client  = new Client();

        foreach ($this->trips() as $route => $routes) {
            $this->route = $route;
            $ports = explode("-", $routes);
            $this->depart_port_name = strtoupper($ports[0]) . " PORT";
            $this->arrival_port_name = strtoupper($ports[1]) . " PORT";

            $crawler = $client->request('POST', 'http://www.cokaliongshipping.com/app/api.cf', ['action' => 3, 'route' => $route]);

            $crawler->filter('tr')->each(function ($node) {
                $scheds = explode(" ", $node->text());
                $sched_day = $scheds[5];
                $sched_day = preg_replace("/\(/", '', $sched_day);
                $sched_day = preg_replace("/\)/", '', $sched_day);
                $depart_time = $scheds[6] . $scheds[7];
                $depart_time = preg_replace('/^0/', '', $depart_time);
                $arrival_time = $scheds[12] . $scheds[13];
                $arrival_time = preg_replace('/^0/', '', $arrival_time);
                
                $this->vessel = $node->filter('td')->first()->text();
                $data = [
                    'vessel' => $node->filter('td')->first()->text(),
                    'depart_time' => trim($depart_time), 
                    'depart_sched' => trim($sched_day), 
                    'arrive_time' => trim($arrival_time), 
                    'company'   => 'Cokaliong Shipping Lines, Inc.',
                    'address'   => 'Cokaliong Tower, Don Sergio Osmeña Avenue, North Reclamation Area, Cebu City',
                    'phone'     => '032 232-7211',
                    'website'   => 'http://www.cokaliongshipping.com',
                    'vessel_type' => 'Passenger',
                    'email'       => 'n/a',
                    'depart_port' => $this->depart_port_name, 
                    'arrive_port' => $this->arrival_port_name
                ];

                $client  = new Client();
                $crawler = $client->request('POST', 'http://www.cokaliongshipping.com/app/api.cf', ['action' => 4, 'route' => $this->route]);
                
                $accoms = $crawler->filter('table')->each(function ($node) {
                    $vessel = $this->vessel;
                    if (preg_match("/$vessel/", $node->text())) {
                        $accoms = [];

                        foreach ($node->filter('tr') as $tr) {
                            if (!preg_match("/$vessel|Room Fare/", $tr->nodeValue)) {
                                $accom_data = explode(" ", $tr->nodeValue);
                                $name = array_shift($accom_data);
                                $price = array_pop($accom_data);
                                $price = preg_replace("/\/.*$/", "", $price);
                                $price = preg_replace("/,/", "", $price);

                                $accoms[] = [
                                    'accomodation' => trim($name),
                                    'detail'       => implode(" ", $accom_data),
                                    'price'        => $price 
                                ];
                            }
                        }

                        return $accoms;
                    }
                });

                if (isset($accoms[1])) {
                    $data['accomodations'] = $accoms[1];
                }
                else if (isset($accoms[0])) {
                    $data['accomodations'] = $accoms[0];
                }
                else {
                    $data['accomodations'] = $accoms[2];
                }

                if (!$data['accomodations']) {
                    die(var_dump($accoms));
                }

                $this->data[] = $data;
            });
        }

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
            $company->setOfficesUrl('http://www.cokaliongshipping.com/#office?1');

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
                $accomodation->setFeatures($accom_data['detail']);

                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($accomodation);
                $em->flush();
            }

            sleep(50);
        }
    }

    public function trips() {
        return [
            7 => 'Cebu-Dumaguete',
            61 => 'Cagayan de Oro-Cebu',
            62 => 'Cagayan de Oro-Jagna',
            4 => 'Calbayog-Cebu',
            60 => 'Cebu-Cagayan de Oro',
            3 => 'Cebu-Calbayog',
            5 => 'Cebu-Dapitan',
            9 => 'Cebu-Iligan',
            11 => 'Cebu-Iloilo',
            49 => 'Cebu-Jagna',
            13 => 'Cebu-Maasin',
            57 => 'Cebu-Masbate',
            44 => 'Cebu-Nasipit',
            17 => 'Cebu-Ozamis',
            19 => 'Cebu-Palompon',
            23 => 'Cebu-Surigao',
            6 => 'Dapitan-Cebu',
            30 => 'Dapitan-Dumaguete',
            8 => 'Dumaguete-Cebu',
            29 => 'Dumaguete-Dapitan',
            10 => 'Iligan-Cebu',
            34 => 'Iligan-Ozamis',
            12 => 'Iloilo-Cebu',
            63 => 'Jagna-Cagayan de Oro',
            46 => 'Jagna-Nasipit',
            14 => 'Maasin-Cebu',
            31 => 'Maasin-Surigao',
            58 => 'Masbate-Cebu',
            48 => 'Nasipit-Cebu',
            47 => 'Nasipit-Jagna',
            18 => 'Ozamis-Cebu',
            33 => 'Ozamis-Iligan',
            20 => 'Palompon-Cebu',
            24 => 'Surigao-Cebu',
            32 => 'Surigao-Maasin',
        ];
    }
}
