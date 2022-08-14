<?php

namespace App\Console\Commands\Command;

use App\Helpers\RedisHelper;
use App\Jobs\Admin\NotifyCommandNotAssignedJob;
use App\Models\Delivery;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class AssignDeliveryCommand extends Command
{
    private $redis_helper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commands:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'look for nearest Delivery from supplier and send request to delivery accept job';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->redis_helper = new RedisHelper();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $commands = \App\Models\Command::whereIn('cycle',['AUTHORIZED','PRE_ASSIGN'])->get()->each(function ($command){
            switch ($command->cycle){
                case 'PRE_ASSIGN':
                    $this->ComputePreAssignedCase($command);
                    break;
                case 'AUTHORIZED':
                    $this->ComputePendingCase($command);
                    break;
                default:
                    throw new \Exception('Unexpected value');

            }
        });
    }

    private function ComputePreAssignedCase($command)
    {
        if($this->ComputeTrialsLimit($command)) return 1;

        if($this->ComputeAssigningLimit($command)) return 1;

        return (new Carbon($command->cycle_at))->diffInMinutes(Carbon::now()) >= $this->redis_helper->getPreAssignedTimeLimit() ? $this->AttempAssignDeliveries($command, true) : 1;

    }

    private function ComputePendingCase($command)
    {
        if($this->ComputeTrialsLimit($command)) return 1;
        return $this->AttempAssignDeliveries($command);

    }

    private function CalculateDistance($from,$to)
    {
        // feature call google maps API

        return 1;
    }

    private function ComputeTrialsLimit($command)
    {
        if($this->redis_helper->getDeliveryAssignTrials($command->id) >= $this->redis_helper->getDeliveryAssignTrialsLimit()){
            $command->cycle = 'PRE_ASSIGN_ADMIN';
            $command->cycle_at = Carbon::now();
            $command->save();
            dispatch(new NotifyCommandNotAssignedJob($command));
            return 1;
        }
        return 0;
    }

    private function ComputeAssigningLimit($command)
    {
        $pre_asigned = $this->redis_helper->getAllPreAssignedDeliveriesToCommand($command->id);
        if(count($pre_asigned) >= $this->redis_helper->getAssignDeliveriesLimit()){
            //notify admin todo
            // change command cycle
            $command->cycle = 'PRE_ASSIGN_ADMIN';
            $command->cycle_at = Carbon::now();
            $command->save();
            dispatch(new NotifyCommandNotAssignedJob($command));
            return 1;
        }
        return 0;
    }

    private function GetFreeDeliveriesList($command, $pre_assigned)
    {
        $supplier = Supplier::findOrFail($command->supplier_id);
        $deliveries = Delivery::where('available',1)->where('cycle','OFF')->whereNotIn('id',$pre_assigned ? ($this->redis_helper->getAllPreAssignedDeliveriesToCommand($command->id) ?? []) : [])->get()->map(function ($item) use ($supplier){
            return (object)[
                "model" => $item,
                "id" => $item->id,
                "distance" => $this->CalculateDistance(["lat"=> $item->lat, "long"=> $item->long],["lat"=> $supplier->lat, "long"=> $supplier->long])
            ] ;
        });

        return $deliveries;
    }

    private function AttempAssignDeliveries($command,$pre_assigned = false)
    {
        $deliveries = $this->getFreeDeliveriesList($command,$pre_assigned);
        if($deliveries->count()){
            $pre_assinged_delivery = $deliveries->sortBy('distance')->values()->first();
            $this->redis_helper->preAssignDeliveryToCommand($pre_assinged_delivery->id, $command->id);
            $command->cycle = 'PRE_ASSIGN';
            $command->cycle_at = Carbon::now();
            $command->save();
            // send notification to admin & delivery
            return 1;
        }else{
            $redis_helper = new RedisHelper();
            $redis_helper->incrDeliveryAssignTrials($command->id);
            return 1;
        }
    }

}
