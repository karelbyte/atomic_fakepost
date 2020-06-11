<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class PostRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:atomic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fake Post Atomic ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

         /*
         * Well, then, the objective is to "delay", but not to lose processes in traffic jams
         * (all within a while true or with a cut-off condition when your list of fictitious requests of thousands of shipments is emptied)
         * You take x number of requests until you reach your Limit when a request returns, if it is good, you take another one from the list.
         * It can be a stack and save me problems of reordering the array if it fails,
         * returning the initial data to the stack, the idea is that it does not fail so you do not have to reorganize the array,
         * but if you put it in a position that is not the next to leave for example, if request 3 fails, do not return request 3, and take request 3.
         * I put it a little away from the point where you are taking in the end, this depends a lot on how the structure is implemented,
         * or the importance of the request
         */

        // POST REQUEST SIMULATION
        $requests = new Collection();
        for ($i = 0; $i <= 10000000; $i++) {
            $requests->push([
                'fake_item_1' => "item_${i}",
                'fake_item_2' => "item_" . ($i + 1),
            ]);
        }

        while ($requests->isNotEmpty()) {
            $payload = $requests->shift(); // take out the first item and return it
            // We control the sending of the request with a try-catch to ensure any exception
            try {
                $response = Http::post('https://atomic.incfile.com/fakepost', $payload);
                if ($response->successful()) {
                    echo "The server replied OK :)";
                } else {
                    // If no exception is answered but the server for some reason does not respond
                    // correctly we also send the request to the stack for a next attempt
                    $requests->push($payload);
                }
            } catch (\Exception $e) {
                // An exception has occurred and we send that request to the stack for a next attempt
                $requests->push($payload);
            }
        }
    }
}
