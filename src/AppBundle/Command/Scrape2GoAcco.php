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
use AppBundle\Entity\VesselAccomodations;
use AppBundle\Entity\SeaPorts;

use Goutte\Client;

class Scrape2GoAcco extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:site2goacco')
             ->addArgument('origin', InputArgument::REQUIRED, 'origin required')
             ->addArgument('destination', InputArgument::REQUIRED, 'destination required')
             ->addArgument('date', InputArgument::REQUIRED, 'date required')
             ->addArgument('vessel', InputArgument::REQUIRED, 'vessel required')
             ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $client  = new Client();
        $crawler = $client->request('GET', 'http://travel.2go.com.ph/eTicket/search.asp');

        $form = $crawler->selectButton('')->form();
        $form->disableValidation();
        $crawler = $client->submit($form, array('adult' => '1', 'ins' => 'N', 'origin' => $input->getArgument('origin'), 'destination' => $input->getArgument('destination'), 'depart_date' => $input->getArgument('date')));

        $accom = $crawler->filter('input[type="radio"]')->each(function ($node) {
            $client  = new Client();
            $price_url = 'http://travel.2go.com.ph/eTicket/total_fare.asp?str='.$node->attr('value').'~1~0~N';
            $accomcode = preg_replace("/\|.*$/", "", $node->attr('value'));
            $accom_data = $this->getAccom($accomcode);

            if ($accom_data) {
                $crawler = $client->request('GET', $price_url);

                $accom_data['price'] = preg_replace("/,/", "", $crawler->filter('#total_amount')->text());
            }
            else {
                die("This code is not yet added $accomcode\n");
            }
    
            return $accom_data;
        });

        $origdest = $crawler->filter('tr[valign="baseline"]')->each(function ($node) {
            if (preg_match('/Origin/', $node->text())) {
                $data = explode("\n", $node->text()); 

                return trim($data[5]).";".trim($data[13]);
            }
        });

        $ports = explode(";", $origdest[1]);

        $dep_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
            'name' => strtoupper($ports[0])." PORT"
        ]);
        $arrive_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
            'name' => strtoupper($ports[1])." PORT"
        ]);

        $company = $this->getContainer()->get('doctrine')->getRepository(Companies::class)->findOneByName('2GO Group Inc.');

        $time = explode(" ", $input->getArgument('date'));
        $time1 = $time[1];
        $time1 = preg_replace("/:00$/", "", $time1);
        $time1 = preg_replace("/^0/", "", $time1);
        $time = $time1.$time[2];
        $time = preg_replace("/~.*$/", "", $time);

        $vessel = $this->getContainer()->get('doctrine')->getRepository(CompanyVessels::class)->findOneBy([
            'company' => $company,
            'departPort' => $dep_port,
            'arrivePort' => $arrive_port,
            'name'      => $input->getArgument('vessel'),
            'departTime' => $time
        ]);
        
        if ($vessel) {
            foreach ($accom as $accom_data) {
                $accomodation = $this->getContainer()->get('doctrine')->getRepository(VesselAccomodations::class)->findOneBy([
                    'accomodation'  => $accom_data['name'],
                    'vessel' => $vessel
                ]);

                if (!$accomodation) {
                    $accomodation = new VesselAccomodations();
                    $accomodation->setVessel($vessel);
                    $accomodation->setAccomodation($accom_data['name']);
                }

                $accomodation->setPrice($accom_data['price']);
                $accomodation->setFeatures($accom_data['details']);

                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($accomodation);
                $em->flush();
            }
        }
    }

    public function getAccom($accom_code) {
        $accoms = [
            'SUPER~SUPER' => [
                'name' => 'Super Value',
                'details' => 'Aircon with bed, twin upper/lower deck'
            ],
            'TOUR~TOUR' => [
                'name' => 'Tourist',
                'details' => 'Aircon with bed, single upper/lower deck'
            ],
            'CAB4W~CABIN' => [
                'name' => 'Cabin',
                'details' => 'Aircon nice bed, 4 person room'
            ],
            'STR3~STR3' => [
                'name' => 'State Room',
                'details' => 'Aircon room with 3 single beds'
            ],
        ];

        return $accoms[$accom_code];
    }
}
