@extends('admin.master', ['menu' => 'cms', 'submenu' => 'wholesale_requests'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{ __('Wholesale Requests') }}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Wholesale Requests') }}</li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="customers__area bg-style mb-30">
                <div class="customers__table">
                    <table class="dataTableHover row-border table-style table table-striped table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Company Name') }}</th>
                            <th>{{ __('Contact Name') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Services') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($requests as $req)
                            <tr>
                                <td>{{ $req->id }}</td>
                                <td>{{ $req->company_name }}</td>
                                <td>{{ $req->contact_name }}</td>
                                <td>{{ $req->contact_phone }}</td>
                                <td>
                                    @if(is_array($req->services))
                                        @foreach($req->services as $service)
                                            @php
                                                $serviceLabel = '';
                                                switch($service) {
                                                    case 'beans': $serviceLabel = __('new_design.wholesale.service_beans') ?? 'Coffee Supply'; break;
                                                    case 'equipment': $serviceLabel = __('new_design.wholesale.service_equip') ?? 'Equipment'; break;
                                                    case 'training': $serviceLabel = __('new_design.wholesale.service_train') ?? 'Training'; break;
                                                    case 'maintenance': $serviceLabel = __('new_design.wholesale.service_maintenance') ?? 'Maintenance'; break;
                                                    default: $serviceLabel = $service;
                                                }
                                            @endphp
                                            <span class="badge bg-secondary text-white m-1">{{ $serviceLabel }}</span>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.wholesale.update_status', $req->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="form-select form-select-sm status-select" style="width: auto; min-width: 120px;">
                                            <option value="0" class="text-warning" {{ $req->status == 0 ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                            <option value="1" class="text-success" {{ $req->status == 1 ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                            <option value="2" class="text-danger" {{ $req->status == 2 ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="action__buttons">
                                        <a href="javascript:void(0)" class="btn-action" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}" title="{{ __('View') }}"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('admin.wholesale.delete', $req->id) }}" class="btn-action delete text-danger" onclick="return confirm('{{ __('Are you sure you want to delete this request?') }}')"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('No Wholesale Requests Found!') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    
                    <div class="d-flex justify-content-end mt-3">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for details -->
    @foreach ($requests as $req)
        <div class="modal fade" id="viewModal{{ $req->id }}" tabindex="-1" role="dialog" aria-labelledby="viewModalTitle{{ $req->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="viewModalLongTitle">{{ __('Wholesale Request Details') }}</h5>
                        <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">{{ __('Company Name') }}:</h6>
                            <p class="text-muted fs-5">{{ $req->company_name }}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">{{ __('Contact Name') }}:</h6>
                            <p class="text-muted fs-5">{{ $req->contact_name }}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">{{ __('Phone') }}:</h6>
                            <p class="text-muted fs-5">{{ $req->contact_phone }}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">{{ __('Estimated Monthly Qty') }}:</h6>
                            <p class="text-muted fs-5">{{ $req->estimated_qty }}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">{{ __('Required Services') }}:</h6>
                            <div>
                                @if(is_array($req->services))
                                    @foreach($req->services as $service)
                                        @php
                                            switch($service) {
                                                case 'beans': $serviceLabel = __('new_design.wholesale.service_beans') ?? 'Coffee Supply'; break;
                                                case 'equipment': $serviceLabel = __('new_design.wholesale.service_equip') ?? 'Equipment'; break;
                                                case 'training': $serviceLabel = __('new_design.wholesale.service_train') ?? 'Training'; break;
                                                case 'maintenance': $serviceLabel = __('new_design.wholesale.service_maintenance') ?? 'Maintenance'; break;
                                                default: $serviceLabel = $service;
                                            }
                                        @endphp
                                        <span class="badge bg-secondary text-white m-1fs-6">{{ $serviceLabel }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">{{ __('Additional Notes') }}:</h6>
                            <p class="text-muted bg-light p-2 rounded" style="white-space: pre-line;">{{ $req->notes ?? __('None') }}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold mb-1">{{ __('Commercial Register / Signboard') }}:</h6>
                            @if($req->cr_or_signboard)
                                @php
                                    $fileExt = strtolower(pathinfo($req->cr_or_signboard, PATHINFO_EXTENSION));
                                    $filePath = asset('uploaded_files/wholesale/' . $req->cr_or_signboard);
                                @endphp
                                @if($fileExt === 'pdf')
                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary text-white" style="background-color: #0d6efd; border-color: #0d6efd; padding: 6px 12px;">
                                        <i class="fas fa-file-pdf me-1"></i> {{ __('View PDF Document') }}
                                    </a>
                                @else
                                    <a href="{{ $filePath }}" target="_blank" class="d-block mb-2">
                                        <img src="{{ $filePath }}" class="img-fluid img-thumbnail" style="max-height: 200px;" alt="Signboard Image">
                                    </a>
                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-primary text-white" style="background-color: #0d6efd; border-color: #0d6efd; padding: 6px 12px;">
                                        <i class="fas fa-external-link-alt me-1"></i> {{ __('Open Full Image') }}
                                    </a>
                                @endif
                            @else
                                <p class="text-danger">{{ __('No document uploaded') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
