<?php namespace Acmee;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\ClientInterface;
use ZipArchive;


class NewCommand extends Command{

  private $client;

  public function __construct(ClientInterface $client){
    $this->client = $client;
    parent::__construct();
  }
  protected function configure(){
    $this
      ->setName("new")
      ->setDescription("Create New Laravel Application")
      ->addArgument('directory', InputArgument::REQUIRED, 'directory name');
  }

  protected function execute(InputInterface $input, OutputInterface $output){
    $directory = getcwd(). DIRECTORY_SEPARATOR . $input->getArgument('directory');
    $this->checkDirectoryExists($directory, $output);
    $output->writeln("<comment>Crafting application...</comment>");
    $this->download($zipFile = $this->makeFileName())
      ->extract($zipFile, $directory)
      ->cleanUp($zipFile);

    $output->writeln("<info>Application Ready!!</info>");

  }

  protected function checkDirectoryExists($directory, $output){
    if(is_dir($directory)){
      $output->writeln("<error>Application already exists</error>");
      exit(1);
    }
  }


  protected function makeFileName(){
    return getcwd().DIRECTORY_SEPARATOR."laravel_".md5(time().uniqid()).".zip";
  }
  protected function download($zipFile){
    $response = $this->client->get("http://cabinet.laravel.com/latest.zip")->getBody();
    file_put_contents($zipFile, $response);
    return $this;
  }

  protected function extract($zipFile, $directory){
    $zip = new ZipArchive;
    $zip->open($zipFile);
    $zip->extractTo($directory);
    $zip->close();
    return $this;
  }

  protected function cleanUp($zipFile){
    @chmod($zipFile, 0777);
    @unlink($zipFile);
    return $this;
  }

}