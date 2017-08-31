<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\TownCities;

use Goutte\Client;

class ScrapeCities extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:phcities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $client  = new Client();
        $crawler = $client->request('GET', 'http://nap.psa.gov.ph/activestats/psgc/listcity.asp');

        $crawler->filter('table > tr')->each(function ($node) {
            if (!$node->attr('bgcolor') && !preg_match('/Select an/', $node->text())) {
                $info = explode("\n", $node->text());

                $city = preg_replace("/\r/", "", $info[4]);
                $city = preg_replace("/^\s+/", "", $city);
                $city = preg_replace("/\(.*$/", "", $city);
                $city = preg_replace("/\s+$/", "", $city);

                $prov = preg_replace("/\r/", "", $info[13]);
                $prov = preg_replace("/\(.*$/", "", $prov);
                $prov = preg_replace("/^\s+/", "", $prov);
                $prov = preg_replace("/\s+$/", "", $prov);

                $check = $this->getContainer()->get('doctrine')->getRepository(TownCities::class)->findOneBy([
                    'townCity' => $city,
                    'province' => $prov
                ]);

                if (!$check) {
                    $town_city = new TownCities();
                    $town_city->setTownCity($city);
                    $town_city->setProvince($prov);

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $em->persist($town_city);
                    $em->flush();
                }
            }
        });
    }
}
