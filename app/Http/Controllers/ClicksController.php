<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ClicksController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = $request->attributes->get('user_details')['id'];

        $records = Click::whereHas('url', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->cursorPaginate(10);

        return $this->successResponse($records);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $userId = $request->attributes->get('user_details')['id'];

        $record = Click::where('id', $id)
            ->whereHas('url', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->delete();

        if ($record) {
            return $this->successResponse($record, 'Click deleted successfully.');
        }

        return $this->errorResponse('Unauthorized - Cannot delete click.', null, 401);

    }
}
