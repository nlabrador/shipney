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

class Scrape2Go extends ContainerAwareCommand
{
    private $depart_port_name;
    private $arrive_port_name;

    protected function configure()
    {
        $this->setName('scrape:site2go')
             ->addArgument('origin', InputArgument::REQUIRED, 'origin required')
             ->addArgument('destination', InputArgument::REQUIRED, 'destination required')
             ->addArgument('date', InputArgument::REQUIRED, 'date required')
             ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

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
                    $this->depart_port_name = preg_replace("/CITY|CITY OF|JETTY|PORT/", "", $voy[0]);
                    $this->arrive_port_name = preg_replace("/CITY|CITY OF|JETTY|PORT/", "", $voy[1]);
                }

                if (!preg_match('/Departure|Open Voyages|Time/', $node->text())) {
                    $fields = explode("\n", $node->text());
                    $vessel_names = array_map(function ($name) { return ucfirst(strtolower($name)); }, explode(" ", trim($fields[0])));

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
