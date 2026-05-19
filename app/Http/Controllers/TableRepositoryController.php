<?php

namespace App\Http\Controllers;

use App\Models\TableRepository;

class TableRepositoryController extends Controller
{
    public function index()
    {
        $tables = TableRepository::with('columns')
            ->orderBy('table_name')
            ->paginate(20);

        return view('repository.index', compact('tables'));
    }

    public function show(TableRepository $table)
    {
        $table->load('columns', 'sourceStory');

        return view('repository.show', compact('table'));
    }
}
