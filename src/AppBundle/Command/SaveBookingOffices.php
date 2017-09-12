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
use AppBundle\Entity\CompanyBookings;

class SaveBookingOffices extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('save:bookoffices');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $dir = $this->getContainer()->get('kernel')->getRootDir() . '/../company_offices/';

        foreach (scandir($dir) as $codir) {
            if ( ! in_array($codir, ['.', '..'])) {
                $this->save($dir . $codir);
            }
        }

    }

    public function save($dir) {
        $offices = array_map('str_getcsv', file($dir."/offices.csv"));

        foreach ($offices as $office) {
            if ($office[0] == 'Company') {
                continue;
            }
            else {
                $company = $this->getContainer()->get('doctrine')->getRepository(Companies::class)->findOneByName($office[0]);

                if ($company) {
                    $address = $office[1];

                    $check_office = $this->getContainer()->get('doctrine')->getRepository(CompanyBookings::class)->findOneByAddress($address);

                    if (!$check_office) {
                        $com_office = new CompanyBookings();
                        $com_office->setAddress($address);
                        $com_office->setPhone($office[2]);
                        $com_office->setCompany($company);

                        $em = $this->getContainer()->get('doctrine')->getManager();
                        $em->persist($com_office);
                        $em->flush();
                    }
                }
            }
        }
    }
}
