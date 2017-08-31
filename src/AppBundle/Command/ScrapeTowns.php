<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\TownCities;

use Goutte\Client;

class ScrapeTowns extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('scrape:phtowns');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        for ($i= 1; $i<=75; $i++) {
            echo "Processing page $i\n";

            $client  = new Client();
            $crawler = $client->request('GET', 'http://nap.psa.gov.ph/activestats/psgc/listmun.asp?whichpage='. $i .'&pagesize=20&sqlquery=select+municipalities.name%2C+municipalities.regprovmunbgy%2C+municipalities.incomeclass%2C+municipalities.reg%2C+municipalities.prov%2C+municipalities.mun%2C+municipalities.submun%2C+municipalities.city%2C+province.prov%2C+province.name%2C+municipalities.jan2010regvoters%2C+municipalities.a2010Pop%2C+municipalities.landarea2007+from+municipalities+INNER+JOIN+Province+ON+Municipalities.Prov+%3D+Province.Prov+where+municipalities.submun%3D0+and+municipalities.city%3D0+ORDER+BY+municipalities.name');

            $crawler->filter('table > tr')->each(function ($node) {
                if (!$node->attr('bgcolor') && !preg_match('/Select an/', $node->text())) {
                    $info = explode("\n", $node->text());

                    $city = preg_replace("/\r/", "", $info[2]);
                    $city = preg_replace("/^\s+/", "", $city);
                    $city = preg_replace("/\(.*$/", "", $city);
                    $city = preg_replace("/\s+$/", "", $city);
                    $prov = preg_replace("/\r/", "", $info[12]);
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
}
