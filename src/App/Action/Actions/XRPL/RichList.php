<?php
namespace App\Action\Actions\XRPL;

use App\Action\BaseAction;
use App\Services\XRPL\CalcRichListService;
use App\Variable;

class RichList extends BaseAction
{
    public function __construct()
    {
        parent::__construct();

        $this->setLayout('default');
        $this->setView('website/richlist');

        $project = $this->getRequest()->getParam('action');
        $projectName = $project; // @todo replace

        try {
            $service = new CalcRichListService($project);
            $countsPerWallet = $service->getCountsPerWalletFromCache();
            if (!$countsPerWallet) {
                $countsPerWallet = $service->getCountsPerWallet();
            }
        } catch (\Exception $e) {
            abort('RichList for ' . $projectName . ' almost ready, try again later.', 'danger');
        }

        $this->setVariable(new Variable('projectName', $projectName));
        $this->setVariable(new Variable('countsPerWallet', $countsPerWallet));
        $this->setVariable(new Variable('collections', $service->getCountsPerWalletBluePrint()['collections']));
    }

    public function run()
    {
        parent::run();

        return $this;
    }
}