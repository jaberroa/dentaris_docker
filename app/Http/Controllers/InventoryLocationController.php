<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\CreateInventoryLocationStockRequest;
use App\Http\Requests\Inventory\StoreInventoryLocationRequest;
use App\Http\Requests\Inventory\UpdateInventoryLocationRequest;
use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Services\InventoryLocationService;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class InventoryLocationController extends Controller
{
    public function index(Request $request)
    {
        $context = $this->clinicContext($request);
        $locations = InventoryLocation::query()
            ->forClinic($context)
            ->withCount(['inventories' => fn ($query) => $query->forClinic($context)])
            ->withSum(['inventories' => fn ($query) => $query->forClinic($context)], 'current_stock')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(20);

        return view('inventory.locations.index', compact('locations'));
    }

    public function store(StoreInventoryLocationRequest $request)
    {
        $context = $request->clinicContext();
        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        $location = new InventoryLocation($request->validated() + ['is_active' => true]);
        $location->forceFill(['clinic_id' => $context->clinicId])->save();

        return redirect()->route('inventory.locations.index')->with('success', 'Ubicación creada correctamente.');
    }

    public function update(UpdateInventoryLocationRequest $request, InventoryLocation $inventoryLocation)
    {
        $context = $request->clinicContext();
        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');
        abort_unless((int) $inventoryLocation->clinic_id === $context->clinicId, 404);
        $inventoryLocation->update($request->validated());

        return redirect()->route('inventory.locations.index')->with('success', 'Ubicación actualizada correctamente.');
    }

    public function createStockLocation(
        CreateInventoryLocationStockRequest $request,
        Inventory $inventory,
        InventoryLocationService $locationService,
    ) {
        $context = $request->clinicContext();
        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');
        abort_unless((int) $inventory->clinic_id === $context->clinicId, 404);

        try {
            $result = $locationService->assignOrCreateStockLocation(
                $inventory,
                (int) $request->validated('inventory_location_id'),
                $request->user(),
                $context,
            );
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

    private function clinicContext(Request $request): ClinicContext
    {
        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');

        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        return $context;
    }
}
