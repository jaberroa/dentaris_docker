<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\CreateInventoryLocationStockRequest;
use App\Http\Requests\Inventory\StoreInventoryLocationRequest;
use App\Http\Requests\Inventory\UpdateInventoryLocationRequest;
use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Services\InventoryLocationService;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class InventoryLocationController extends Controller
{
    public function index()
    {
        $locations = InventoryLocation::query()
            ->withCount('inventories')
            ->withSum('inventories', 'current_stock')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(20);

        return view('inventory.locations.index', compact('locations'));
    }

    public function store(StoreInventoryLocationRequest $request)
    {
        InventoryLocation::query()->create($request->validated() + ['is_active' => true]);

        return redirect()->route('inventory.locations.index')->with('success', 'Ubicación creada correctamente.');
    }

    public function update(UpdateInventoryLocationRequest $request, InventoryLocation $inventoryLocation)
    {
        $inventoryLocation->update($request->validated());

        return redirect()->route('inventory.locations.index')->with('success', 'Ubicación actualizada correctamente.');
    }

    public function createStockLocation(
        CreateInventoryLocationStockRequest $request,
        Inventory $inventory,
        InventoryLocationService $locationService,
    ) {
        try {
            $result = $locationService->assignOrCreateStockLocation($inventory, (int) $request->validated('inventory_location_id'), $request->user());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['inventory_location_id' => $exception->getMessage()]);
        }

        return redirect()->route('inventory.index')->with(
            'success',
            $result['created']
                ? 'Nueva ubicación de stock creada. Ya puedes realizar transferencias.'
                : 'Ubicación asignada correctamente al inventario existente.',
        );
    }
}
