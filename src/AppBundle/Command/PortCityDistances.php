<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\SeaPorts;
use AppBundle\Entity\TownCities;
use AppBundle\Entity\Distances;

class PortCityDistances extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('get:pcdistance');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $ports = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findAll();

        foreach ($ports as $port) {
            $port_town_city = $port->getTownCity()->getTownCity() . ", " . $port->getTownCity()->getProvince();

            $em = $this->getContainer()->get('doctrine')->getManager();
            $query = $em->createQuery("
                SELECT tc FROM AppBundle:TownCities tc
                    WHERE tc.province = :province
            ");
            $query->setParameter('province', $port->getTownCity()->getProvince());

            $target_cities = $query->getResult();

            foreach ($target_cities as $city) {
                $check = $this->getContainer()->get('doctrine')->getRepository(Distances::class)->findOneBy([
                    'seaPort'        => $port,
                    'targetTownCity' => $city
                ]);

                if ($check) {
                    continue;
                }
                else {
                    $distance = 0;

                    if ($city->getTownCity() != $port->getTownCity()->getTownCity()) {
                        $target_city = $city->getTownCity() . ", " . $city->getProvince();

                        $from = urlencode($port_town_city);
                        $to   = urlencode($target_city);

                        $data = json_decode(file_get_contents("http://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&language=en-EN&sensor=false"));

                        foreach ($data->rows[0]->elements as $road) {
                            if (isset($road->distance)) {
                                $distance = $road->distance->value;
                            }
                            else {
                                $distance = 1;
                            }

                            break;
                        }
                    }

                    $new_sea_port = new Distances();
                    $new_sea_port->setSeaPort($port);
                    $new_sea_port->setTargetTownCity($city);
                    $new_sea_port->setDistance($distance);

                    $em = $this->getContainer()->get('doctrine')->getManager();
                    $em->persist($new_sea_port);
                    $em->flush();
                }
            }

            echo "Sleeping ...\n";
            sleep(20);
        }
    }
}
