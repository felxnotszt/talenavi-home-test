<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Todo;

class TodoController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string',
        'assignee' => 'nullable|string',
        'due_date' => 'required|date',
        'time_tracked' => 'numeric',
        'status' => 'in:pending,open,in_progress,completed',
        'priority' => 'required|in:low,medium,high',
    ]);

    $todo = Todo::create([
        'title' => $validated['title'],
        'assignee' => $validated['assignee']??null,
        'due_date' => $validated['due_date'],
        'time_tracked' => $validated['time_tracked']??0,
        'status' => $validated['status'] ?? 'pending',
        'priority' => $validated['priority'],
    ]);

    return response()->json([
        'message' => 'Todo created successfully',
        'data' => $todo,
    ]);
}

}
