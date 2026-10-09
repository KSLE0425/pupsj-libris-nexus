<?php

namespace App\Http\Controllers;

use App\Models\ReportPreset;
use Illuminate\Http\Request;

class ReportPresetController extends Controller
{
    /**
     * Get presets for a given report type.
     */
    public function index(Request $request)
    {
        $reportType = $request->query('report_type');
        $query = ReportPreset::query();

        if ($reportType) {
            $query->where('report_type', $reportType);
        }

        return response()->json($query->orderByDesc('is_default')->orderBy('name')->get());
    }

    /**
     * Save a new preset or update existing.
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string|max:50',
            'name'        => 'required|string|max:120',
            'options'     => 'required|array',
            'is_default'  => 'nullable|boolean',
        ]);

        $adminName = auth()->user()->name ?? auth()->user()->email ?? 'Admin';

        if ($request->is_default) {
            ReportPreset::where('report_type', $request->report_type)->update(['is_default' => false]);
        }

        $preset = ReportPreset::create([
            'report_type' => $request->report_type,
            'name'        => trim($request->name),
            'options'     => $request->options,
            'is_default'  => (bool) $request->is_default,
            'created_by'  => $adminName,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Preset \"{$preset->name}\" saved successfully.",
            'preset'  => $preset,
        ]);
    }

    /**
     * Delete a preset.
     */
    public function destroy(ReportPreset $preset)
    {
        $name = $preset->name;
        $preset->delete();

        return response()->json([
            'success' => true,
            'message' => "Preset \"{$name}\" deleted.",
        ]);
    }

    /**
     * Set a preset as the default.
     */
    public function setDefault(ReportPreset $preset)
    {
        ReportPreset::where('report_type', $preset->report_type)->update(['is_default' => false]);
        $preset->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => "Preset \"{$preset->name}\" set as default.",
        ]);
    }
}
