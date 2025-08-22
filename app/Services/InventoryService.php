<?php

namespace App\Services;

use App\Models\Part;
use App\Models\PartMovement;
use App\Models\Technician;
use App\Models\TechnicianPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Передать запчасть технику (менеджер -> техник).
     */
    public function transferToTechnician(Part $part, Technician $tech, int $qty, ?int $fromWarehouseId = null, ?int $toWarehouseId = null): void
    {
        DB::transaction(function () use ($part, $tech, $qty, $fromWarehouseId, $toWarehouseId) {

            // Блокировка строки запчасти, чтобы остаток не «улетел» при параллельных операциях
            $part->lockForUpdate();

            // Нельзя передать больше реального остатка
            $qty = max(0, min($qty, $part->quantity));
            if ($qty === 0) {
                return;
            }

            // Уменьшаем складской остаток
            $part->decrement('quantity', $qty);

            // Пополняем «карман» техника
            /** @var TechnicianPart $techPart */
            $techPart = TechnicianPart::firstOrCreate(
                ['technician_id' => $tech->user_id, 'part_id' => $part->id],
                [
                    'quantity'          => 0,
                    'total_transferred' => 0,
                    'manager_id'        => Auth::id(),
                    'nomenclature_id'   => $part->nomenclature_id,
                    'warehouse_id'      => $part->warehouse_id,
                ]
            );
            $techPart->increment('quantity', $qty);
            $techPart->increment('total_transferred', $qty);

            // Лог движения
            PartMovement::create([
                'part_id'            => $part->id,
                'from_warehouse_id'  => $fromWarehouseId ?? $part->warehouse_id,
                'to_warehouse_id'    => $toWarehouseId,
                'technician_id'      => $tech->user_id,
                'manager_id'         => Auth::id(),
                'quantity'           => $qty,
            ]);
        });
    }

    /**
     * Списать запчасть у техника при «потреблении» (закрытии задачи/подписи клиента).
     */
    public function consumeFromTechnician(Part $part, Technician $tech, int $qty, ?int $toWarehouseId = null): void
    {
        DB::transaction(function () use ($part, $tech, $qty, $toWarehouseId) {
            $techPart = TechnicianPart::where('technician_id', $tech->user_id)
                ->where('part_id', $part->id)
                ->lockForUpdate()
                ->first();

            if (!$techPart) return;

            $qty = max(0, min($qty, $techPart->quantity));
            if ($qty === 0) return;

            // уменьшаем «карман» техники
            $techPart->decrement('quantity', $qty);

            // лог движения «списание у техника» – можно вести через отдельный «склад-списание» или просто фиксировать как движение от техника (from null склад, но technician_id заполнен)
            PartMovement::create([
                'part_id'            => $part->id,
                'from_warehouse_id'  => null,
                'to_warehouse_id'    => $toWarehouseId,  // при необходимости списывать на конкретный склад/подразделение
                'technician_id'      => $tech->user_id,
                'manager_id'         => Auth::id(),
                'quantity'           => $qty,
            ]);
        });
    }

    /**
     * Возврат запчастей от техника на склад.
     */
    public function returnFromTechnician(Part $part, Technician $tech, int $qty, int $toWarehouseId): void
    {
        DB::transaction(function () use ($part, $tech, $qty, $toWarehouseId) {
            $techPart = TechnicianPart::where('technician_id', $tech->user_id)
                ->where('part_id', $part->id)
                ->lockForUpdate()
                ->first();

            if (!$techPart) return;

            $qty = max(0, min($qty, $techPart->quantity));
            if ($qty === 0) return;

            // уменьшаем у техника
            $techPart->decrement('quantity', $qty);

            // пополняем склад
            $part->lockForUpdate();
            $part->increment('quantity', $qty);

            // лог движения
            PartMovement::create([
                'part_id'            => $part->id,
                'from_warehouse_id'  => null, // от техника
                'to_warehouse_id'    => $toWarehouseId,
                'technician_id'      => $tech->user_id,
                'manager_id'         => Auth::id(),
                'quantity'           => $qty,
            ]);
        });
    }
}
