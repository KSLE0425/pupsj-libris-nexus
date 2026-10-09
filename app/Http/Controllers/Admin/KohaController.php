<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KohaSyncLog;
use App\Models\Setting;
use App\Services\KohaApiService;
use Illuminate\Http\Request;

class KohaController extends Controller
{
    public function index()
    {
        $lastLog   = KohaSyncLog::latest()->first();
        $baseUrl   = Setting::getValue('koha_base_url', '');
        $apiKey    = Setting::getValue('koha_api_key', '');
        $enabled   = (bool) Setting::getValue('koha_enabled', 0);

        return view('admin.koha.index', compact('lastLog', 'baseUrl', 'apiKey', 'enabled'));
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'koha_base_url' => 'nullable|url|max:255',
            'koha_api_key'  => 'nullable|string|max:255',
            'koha_enabled'  => 'nullable',
        ]);

        Setting::setValue('koha_base_url', $request->input('koha_base_url', ''));
        Setting::setValue('koha_api_key',  $request->input('koha_api_key', ''));
        Setting::setValue('koha_enabled',  $request->boolean('koha_enabled') ? '1' : '0');

        return back()->with('success', 'Koha configuration saved.');
    }

    public function syncBooks(Request $request)
    {
        $service = new KohaApiService();
        try {
            $records = $service->fetchBibliographicRecords(200);
            $count   = 0;
            foreach ($records as $record) {
                $service->importBook($record);
                $count++;
            }
            KohaSyncLog::create(['action' => 'sync_books', 'status' => 'success', 'records_synced' => $count]);
            return back()->with('success', "Synced {$count} bibliographic records from Koha.");
        } catch (\Throwable $e) {
            KohaSyncLog::create(['action' => 'sync_books', 'status' => 'error', 'records_synced' => 0, 'message' => $e->getMessage()]);
            return back()->with('error', 'Koha sync failed: ' . $e->getMessage());
        }
    }

    public function syncCirculation(Request $request)
    {
        $service = new KohaApiService();
        try {
            $checkouts = $service->syncCirculation();
            KohaSyncLog::create(['action' => 'sync_circulation', 'status' => 'success', 'records_synced' => count($checkouts), 'message' => 'Circulation data fetched; manual review required to map patron IDs.']);
            return back()->with('success', 'Circulation data fetched (' . count($checkouts) . ' records). Review logs for details.');
        } catch (\Throwable $e) {
            KohaSyncLog::create(['action' => 'sync_circulation', 'status' => 'error', 'records_synced' => 0, 'message' => $e->getMessage()]);
            return back()->with('error', 'Circulation sync failed: ' . $e->getMessage());
        }
    }

    public function testConnection(Request $request)
    {
        $service = new KohaApiService();
        $result  = $service->testConnection();
        return response()->json($result);
    }
}
