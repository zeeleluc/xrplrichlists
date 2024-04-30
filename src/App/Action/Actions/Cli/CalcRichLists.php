<?php
namespace App\Action\Actions\Cli;

use App\Action\BaseAction;
use App\Models\User;
use App\RichList\Config;
use App\RichList\Service;

class CalcRichLists extends BaseAction implements CliActionInterface
{
    /**
     * @throws \Exception
     */
    public function run(string $forProject = null)
    {
        foreach ($this->getUserQuery()->getAll() as $user) {

            $project = $user->projectName;

            if ($forProject && $project !== $forProject) {
                continue;
            }

            $countsPerWallet = (new Service($project))->getCountsPerWallet();

            // filter out unwanted wallets for the rich list
            $unwantedWallets = env('WALLETS_IGNORE_' . strtoupper($project));
            if ($unwantedWallets) {
                $unwantedWallets = explode(',', $unwantedWallets);
                foreach ($unwantedWallets as $unwantedWallet) {
                    if (array_key_exists($unwantedWallet, $countsPerWallet)) {
                        unset($countsPerWallet[$unwantedWallet]);
                    }
                }
            }

            $fileName = ROOT . '/data/richlists-cache/' . $project . '.json';
            $result = file_put_contents($fileName, json_encode($countsPerWallet));

            if (!$result) {
                $slack = new \App\Slack();
                $slack->sendErrorMessage('Writing rich list data to file `' . $fileName . '` failed!');
            }
        }
    }
}
