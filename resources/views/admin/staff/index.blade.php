@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="container-fluid">
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Staff Management</li>
                    </ol>
                </div>
                <h4 class="page-title">Staff Management</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Staff List</h4>
                            <p class="text-muted mb-0">Manage all staff members here</p>
                        </div><!--end col-->
                        <div class="col-auto">
                            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus me-1"></i> Add New Staff
                            </a>
                        </div><!--end col-->
                    </div>  <!--end row-->
                </div><!--end card-header-->
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="staff-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff ?? [] as $staffMember)
                                <tr>
                                    <td>{{ $staffMember->id }}</td>
                                    <td>{{ $staffMember->name }}</td>
                                    <td>{{ $staffMember->position }}</td>
                                    <td>{{ $staffMember->email }}</td>
                                    <td>{{ $staffMember->phone }}</td>
                                    <td>
                                        @if($staffMember->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.staff.show', $staffMember->id) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.staff.edit', $staffMember->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.staff.destroy', $staffMember->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No staff members found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($staff) && $staff->hasPages())
                    <div class="mt-4">
                        {{ $staff->links() }}
                    </div>
                    @endif
                </div><!--end card-body-->
            </div><!--end card-->
        </div><!--end col-->
    </div><!--end row-->

</div><!-- container -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#staff-table').DataTable({
            "ordering": true,
            "info": true,
            "searching": true,
            "lengthChange": true,
            "pageLength": 25,
            "language": {
                "lengthMenu": "Show _MENU_ entries",
                "zeroRecords": "No staff found",
                "info": "Showing page _PAGE_ of _PAGES_",
                "search": "Search:",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    });
</script>
@endpush
