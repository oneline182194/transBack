<?php

namespace App\Console\Commands;

use App\Models\Comprobante;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Alice\ApiManagerCurl;
use App\Http\Controllers\ComprobanteController;

class EnviarFacturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía las facturas emitidas en el día';

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
     * @return int
     */
    public function handle()
    {
        $date = date('Y-m-d', strtotime("-1 days"));

        $comprobanteController = new ComprobanteController();
        $comprobanteController->enviarSUNAT($date, false);
    }
}
