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

class SaveCoVessels extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('save:covessels');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    { 
        $dir = $this->getContainer()->get('kernel')->getRootDir() . '/../company_data/';

        foreach (scandir($dir) as $codir) {
            if ( ! in_array($codir, ['.', '..'])) {
                $this->saveCo($dir . $codir);

                exec(sprintf("mv %s %s", $dir.$codir, $dir."/../done_migrate/"));
            }
        }

    }

    public function saveCo($dir) {
        $vessels = array_map('str_getcsv', file($dir."/sked.csv"));

        foreach ($vessels as $vessel) {
            if ($vessel[0] == 'Company') {
                continue;
            }
            else {
                $company = $this->getContainer()->get('doctrine')->getRepository(Companies::class)->findOneByName($vessel[0]);
                $addresses = explode(",", $vessel[1]);
                $email   = $vessel[3] ? preg_replace('/^www\./', 'info@', $vessel[3]) : 'N/A';

                if (!$company) {
                    $company = new Companies();
                    $company->setName($vessel[0]);
                }

                $company->setAddress1(trim($addresses[0]));
                $company->setAddress2(trim($addresses[1]));
                $company->setPhone($vessel[2]);
                $company->setWebsite($vessel[3] ? $vessel[3] : 'N/A');
                $company->setEmail($email);
                
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($company);
                $em->flush();

                $vessel_name = $vessel[4];
                $dport = $vessel[6];
                $aport = $vessel[7];

                $accoms = array_map('str_getcsv', file($dir."/accomodations.csv"));

                $vessel_obj = new CompanyVessels();
                $vessel_obj->setName($vessel_name);
                $vessel_obj->setCompany($company);

                $dep_port_name = strtoupper($vessel[6]) . ' PORT';
                $arrive_port_name = strtoupper($vessel[7]) . ' PORT';

                $dep_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                        'name' => $dep_port_name
                ]);
                $arrive_port = $this->getContainer()->get('doctrine')->getRepository(SeaPorts::class)->findOneBy([
                        'name' => $arrive_port_name
                ]);

                $sched_day = $vessel[10];

                if ($sched_day == 'M/W/F/Sat'){
                    $sched_day = 'Mon/Wed/Fri/Sat';
                }
                if ($sched_day == 'T/Th/Sun'){
                    $sched_day = 'Tue/Thu/Sun';
                }

                $vessel_obj->setVesselType($vessel[5]);
                $vessel_obj->setDepartPort($dep_port);
                $vessel_obj->setArrivePort($arrive_port);
                $vessel_obj->setDepartTime($vessel[8]);
                $vessel_obj->setArriveTime($vessel[9]);
                $vessel_obj->setSchedDay($sched_day);

                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($vessel_obj);
                $em->flush();

                foreach($accoms as $accom) {
                    if ($accom[0] == 'Vessel') {
                        continue;
                    }
                    else {
                        if ($accom[0] == $vessel_name && $accom[4] == $dport && $accom[5] == $aport) {
                            $accomodation = new VesselAccomodations();
                            $accomodation->setVessel($vessel_obj);
                            $accomodation->setAccomodation($accom[1]);
                            $accomodation->setPrice($accom[2]);
                            $accomodation->setFeatures($accom[3]);

                            $em = $this->getContainer()->get('doctrine')->getManager();
                            $em->persist($accomodation);
                            $em->flush();
                        }
                    }
                }
            }
        }
    }
}
