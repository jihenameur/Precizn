<?php

namespace App\Console\Commands\Command;

use App\Helpers\RedisHelper;
use App\Jobs\Admin\AdminNewPreAssignCommandJob;
use App\Jobs\Admin\NotifyCommandNotAssignedJob;
use App\Jobs\Delivery\AssignedCommandToDeliveryJob;
use App\Jobs\Delivery\PreAssignCommandToDeliveryJob;
use App\Models\Delivery;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

class AssignDeliveryCommand extends Command
{
    private $redis_helper;
    public $i;
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
        $this->i = 0;
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

        return 0;
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

    private function CalculateDistance($deliveries, $supplier)
    {
        $from_latlong = '';
        $to_latlong = $supplier->lat . "," . $supplier->long;

        foreach ($deliveries as $delivery) {
            $from_latlong = $from_latlong . ($delivery->lat . "," . $delivery->long. "|");
        }

        $distance_data = file_get_contents(
            'https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=' . $from_latlong . '&destinations=' . $to_latlong . '&key=AIzaSyCYRBZBDovYe4GKiOH2PRyDtTWO6ymAZXA'
        );
        $distance_arr = json_decode($distance_data);
        $distances = array();
        foreach ($distance_arr->rows as $key => $element) {
            $distance = $element->elements[0]->distance->text;
            $distance = preg_replace("/[^0-9.]/", "",  $distance);
            $distance *= 1.609344;
            $distance = number_format($distance, 1, '.', '');
            array_push($distances, $distance);

        }
        return $distances;
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
        echo "----------------------";
        $supplier = Supplier::findOrFail($command->supplier_id);
        $deliveries = Delivery::where('available',1)->whereNotIn('id',$pre_assigned ? ($this->redis_helper->getAllPreAssignedDeliveriesToCommand($command->id) ?? []) : [])->get();
        $distances = $this->CalculateDistance($deliveries,$supplier);
        var_dump($deliveries->count());
        $this->i = 0;
        $deliveries->map(function ($item) use ($distances){
            $bucket =  (object)[
                "model" => $item,
                "id" => $item->id,
                "stack" => $this->redis_helper->getDeliveryStack($item->id) ?? 0,
                "distance" => $distances[$this->i]
            ] ;
            $this->i++;
            echo $this->i;
            return $bucket;
        });
        echo "----------------------";
        return $deliveries;
    }

    private function AttempAssignDeliveries($command,$pre_assigned = false)
    {
        $deliveries = $this->getFreeDeliveriesList($command,$pre_assigned);
        if($deliveries->count()){
            $pre_assinged_delivery = $deliveries->sortBy([
                fn ($a, $b) => $a['cycle'] <=> $b['cycle'],
                fn ($a, $b) => $a['distance'] <=> $b['distance'],
                fn ($a, $b) => $b['stack'] <=> $a['stack'],
            ])->values()->first();
            $this->redis_helper->preAssignDeliveryToCommand($pre_assinged_delivery->id, $command->id);
            $command->cycle = 'PRE_ASSIGN';
            $command->cycle_at = Carbon::now();
            $command->save();
            dispatch(new PreAssignCommandToDeliveryJob($pre_assinged_delivery,$command));
            dispatch(new AdminNewPreAssignCommandJob($command, $pre_assinged_delivery));
         //   dispatch(new AssignedCommandToDeliveryJob($command));
            return 1;
        }else{
            $redis_helper = new RedisHelper();
            $redis_helper->incrDeliveryAssignTrials($command->id);
            return 1;
        }
    }

}
