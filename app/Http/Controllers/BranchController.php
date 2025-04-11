<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Branch::class);
        
        $branches = Branch::with('users')->get();
        return response()->json($branches);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'operating_hours' => 'nullable|string|max:255',
            'branch_code' => 'required|string|max:50|unique:branches',
        ]);

        $branch = Branch::create($validated);
        return response()->json($branch, 201);
    }

    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);
        
        $branch->load('users');
        return response()->json($branch);
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'operating_hours' => 'nullable|string|max:255',
            'branch_code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('branches')->ignore($branch->id),
            ],
            'is_active' => 'sometimes|boolean',
        ]);

        $branch->update($validated);
        return response()->json($branch);
    }

    public function assignUsers(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $validated = $request->validate([
            'users' => 'required|array',
            'users.*.user_id' => 'required|exists:users,id',
            'users.*.is_primary' => 'required|boolean',
        ]);

        DB::transaction(function () use ($branch, $validated) {
            $branch->users()->detach();
            
            foreach ($validated['users'] as $user) {
                $branch->users()->attach($user['user_id'], [
                    'is_primary' => $user['is_primary']
                ]);
            }
        });

        return response()->json($branch->load('users'));
    }
} 