<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use Symfony\Component\HttpFoundation\StreamedResponse;

class TodoExportController extends Controller
{
    public function export(Request $request)
    {
        $query = DB::table('todos');

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('assignee')) {
            $assignees = explode(',', $request->assignee);
            $query->whereIn('assignee', $assignees);
        }

        if ($request->filled('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('priority')) {
            $priorities = explode(',', $request->priority);
            $query->whereIn('priority', $priorities);
        }

        if ($request->filled('due_date_start')) {
            $query->where('due_date', '>=', $request->due_date_start);
        }

        if ($request->filled('due_date_end')) {
            $query->where('due_date', '<=', $request->due_date_end);
        }

        if ($request->filled('time_tracked_min')) {
            $query->where('time_tracked', '>=', $request->time_tracked_min);
        }

        if ($request->filled('time_tracked_max')) {
            $query->where('time_tracked', '<=', $request->time_tracked_max);
        }

        $todos = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Title', 'Assignee', 'Due Date', 'Time Tracked', 'Status', 'Priority'];
        $sheet->fromArray($headers, NULL, 'A1');

        $row = 2;
        $totalTime = 0;
        foreach ($todos as $todo) {
            $sheet->setCellValue("A$row", $todo->title);
            $sheet->setCellValue("B$row", $todo->assignee);
            $sheet->setCellValue("C$row", $todo->due_date);
            $sheet->setCellValue("D$row", $todo->time_tracked);
            $sheet->setCellValue("E$row", $todo->status);
            $sheet->setCellValue("F$row", $todo->priority);

            $totalTime += $todo->time_tracked;
            $row++;
        }

        $sheet->setCellValue("A$row", 'Total Todos: ' . $todos->count());
        $sheet->setCellValue("D$row", 'Total Time: ' . $totalTime);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'todos.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }
}
