<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use AppBundle\Entity\Companies;
use AppBundle\Entity\CompanyVessels;
use AppBundle\Entity\VesselAccomodations;
use AppBundle\Entity\SeaPorts;

class SavePriceRanges extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('save:priceranges');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $vessels = $this->getContainer()->get('doctrine')->getRepository(CompanyVessels::class)->findAll();

        foreach ($vessels as $vessel) {
            $em = $this->getContainer()->get('doctrine')->getManager();           
            $query = $em->createQuery("
                SELECT cv.id, min(v.price) as minimum, max(v.price) as maximum FROM AppBundle:VesselAccomodations v
                    JOIN AppBundle:CompanyVessels cv WITH cv.id = v.vessel
                    WHERE v.vessel = :id GROUP BY cv.id
            ");
            $query->setParameter('id', $vessel);
            $results = $query->getScalarResult();

            if (!$results) {
                continue;
            }

            $min = $results[0]['minimum'];
            $max = $results[0]['maximum'];

            $vessel->setPassPriceRange($min . "-" . $max);

            $em = $this->getContainer()->get('doctrine')->getManager();
            $em->persist($vessel);
            $em->flush();
        }
    }
}
