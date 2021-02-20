@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="{{ route('createBackup') }}" class="btn btn-outline-primary p-2 m-2 btn-sm">
                    <i class="fa fa-plus-circle"></i>
                    Create backup
                </a>
            </div>
            <div class="panel-body">
                <table class="table table-hover table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>File Name</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $key => $backup)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $backup['file_name'] }}</td>
                                <td>{{ $backup['file_size'] }}</td>
                                <td>{{ $backup['last_modified'] }}</td>
                                <td>
                                    <a href="{{ url('backup/download/'.$backup['file_name']) }}" class="btn btn-outline-success btm-xs">
                                        <i class="fa fa-download"></i>
                                        Download
                                    </a>
                                    <a href="{{ url('backup/delete/'.$backup['file_name']) }}" class="btn btn-outline-danger btm-xs">
                                        <i class="fa fa-remove"></i>
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
