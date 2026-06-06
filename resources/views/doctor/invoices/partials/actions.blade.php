<div class="btn-group" role="group">
    <a href="{{ route('doctor.invoices.show', $invoice->id) }}" class="btn btn-info btn-sm" title="{{ __('master.view') }}">
        <i class="icon-base ti tabler-eye"></i>
    </a>
    <a href="{{ route('doctor.invoices.print', $invoice->id) }}" class="btn btn-secondary btn-sm" title="{{ __('master.print_invoice') }}" target="_blank">
        <i class="icon-base ti tabler-printer"></i>
    </a>
</div>
