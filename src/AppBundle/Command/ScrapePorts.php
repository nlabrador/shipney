<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\SeaPorts;
use AppBundle\Entity\TownCities;

use Goutte\Client;

class ScrapePorts extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:phseaports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $client  = new Client();
        $crawler = $client->request('GET', 'https://www.silent-gardens.com/sea-ports.php');

        $crawler->filter('.row')->each(function ($node) {
            if (preg_match('/City:/', $node->text())) {
                $port_name = preg_replace("/City:.*$/", "", $node->text());
                $port_name = trim(strtoupper($port_name)) . " PORT" ;
                $city = preg_replace("/.*City:/", "", $node->text());
                $city = preg_replace("/Province:.*$/", "", $city);
                $city = trim(strtoupper($city));
                $prov = preg_replace("/.*Province:/", "", $node->text());
                $prov = preg_replace("/Island:.*$/", "", $prov);
                $prov = trim(strtoupper($prov));

                if ($city == 'BACOLOD') {
                    $city = 'BACOLOD CITY';
                    $prov = 'NEGROS OCCIDENTAL';
                }
                if ($city == 'BAGO') {
                    $city = 'BAGO CITY';
                    $prov = 'NEGROS OCCIDENTAL';
                }
                if ($city == 'BATAAN') {
                    $city = 'MARIVELES';
                }
                if ($city == 'BAYBAY') {
                    $city = 'CITY OF BAYBAY';
                    $prov = 'LEYTE';
                }
                if ($city == 'BENONI') {
                    $city = 'MAHINOG';
                }
                if ($city == 'CATICLAN') {
                    $city = 'MALAY';
                }
                if ($city == 'DAVAO') {
                    $prov = 'DAVAO DEL SUR';
                }
                if ($city == 'DUMAGUETE') {
                    $prov = 'NEGROS ORIENTAL';
                }
                if ($city == 'DUMAGUIT') {
                    $city = 'NEW WASHINGTON';
                }
                if ($city == 'ISABELA') {
                    $prov = 'CITY OF ISABELA';
                }
                if ($city == 'LILO-AN') {
                    $city = 'LILOAN';
                }
                if ($city == 'LILOAN' && $prov == 'LEYTE') {
                    $city = 'LILOAN';
                    $prov = 'SOUTHERN LEYTE'; 
                }
                if ($city == 'MAASIN') {
                    $prov = 'SOUTHERN LEYTE';
                }
                if ($city == 'MANILA') {
                    $prov = 'NCR, CITY OF MANILA, FIRST DISTRICT';
                }
                if ($city == 'OZAMIS') {
                    $city = 'OZAMIZ';
                }
                if ($city == 'SABANG') {
                    $city = 'SAN JOSE';
                }
                if ($city == 'SAN AUGUSTIN') {
                    $city = 'SAN AGUSTIN';
                }
                if ($prov == 'SABAH') {
                    return;
                }
                if ($city == 'SIARGAO') {
                    $city = 'DAPA';
                }
                if ($city == 'SIBULAN') {
                    $prov = 'NEGROS ORIENTAL';
                }
                if ($city == 'SOGOD' && $prov == 'LEYTE') {
                    $prov = 'SOUTHERN LEYTE';
                }
                if ($city == 'SUBIC BAY') {
                    $city = 'SUBIC';
                    $prov = 'ZAMBALES';
                }
                if ($city == 'TAMPI') {
                    $city = 'SAN JOSE';
                }
                if ($city == 'TUBOD' && $prov == 'MISAMIS OCCIDENTAL') {
                    return;
                }
                if ($city == 'ZAMBOANGA' && $prov == 'ZAMBOANGA') {
                    $prov = 'ZAMBOANGA DEL SUR';
                }

                $town_city = $this->getContainer()->get('doctrine')->getRepository(TownCities::class)->findOneBy([
                    'townCity' => $city,
                    'province' => $prov
                ]);

                if (!$town_city) {
                    $city .= " CITY";
                
                    $town_city = $this->getContainer()->get('doctrine')->getRepository(TownCities::class)->findOneBy([
                        'townCity' => $city,
                        'province' => $prov
                    ]);
                    
                    if (!$town_city) {
                        $city = preg_replace("/ CITY/", "", $city);
                        $city = "CITY OF " . $city;
                    
                        $town_city = $this->getContainer()->get('doctrine')->getRepository(TownCities::class)->findOneBy([
                            'townCity' => $city,
                            'province' => $prov
                        ]);
                    }
                }

                if ($town_city) {
                    $check = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                        'name'     => $port_name,
                        'townCity' => $town_city
                    ]);

                    if (!$check) {
                        $port = new SeaPorts();
                        $port->setName($port_name);
                        $port->setTownCity($town_city);

                        $em = $this->getContainer()->get('doctrine')->getManager();
                        $em->persist($port);
                        $em->flush();
                    }
                }
                else {
                    die("Did not found $city - $prov");
                }
            }
        });
    }
}
