<x-app-layout>
    @push('styles')
    @endpush

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('master.frequently_asked_questions') }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <form method="GET" action="{{ route('laboratory.faq.index') }}">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="{{ __('master.search_faqs') }}" 
                                               value="{{ $search }}">
                                        <button class="btn btn-outline-primary" type="submit">
                                            <i class="ti ti-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form method="GET" action="{{ route('laboratory.faq.index') }}">
                                    @if($search)
                                        <input type="hidden" name="search" value="{{ $search }}">
                                    @endif
                                    <select name="category" class="form-select" onchange="this.form.submit()">
                                        <option value="">{{ __('master.all_categories') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    {{ $categoryId == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>

                        <!-- Categories -->
                        @if(!$search && !$categoryId)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">{{ __('master.browse_by_category') }}</h6>
                                    <div class="row">
                                        @foreach($categories as $category)
                                            <div class="col-md-3 mb-3">
                                                <a href="{{ route('laboratory.faq.category', $category->slug) }}" 
                                                   class="card text-decoration-none h-100">
                                                    <div class="card-body text-center">
                                                        @if($category->icon)
                                                            <i class="{{ $category->icon }} text-{{ $category->color }}" 
                                                               style="font-size: 2rem;"></i>
                                                        @endif
                                                        <h6 class="card-title mt-2">{{ $category->name }}</h6>
                                                        <small class="text-muted">{{ $category->activeFaqs->count() }} {{ __('master.questions') }}</small>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- FAQ List -->
                        <div class="row">
                            <div class="col-12">
                                @if($faqs->count() > 0)
                                    <div class="accordion" id="faqAccordion">
                                        @foreach($faqs as $index => $faq)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading{{ $faq->id }}">
                                                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" 
                                                            type="button" data-bs-toggle="collapse" 
                                                            data-bs-target="#collapse{{ $faq->id }}" 
                                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                                            aria-controls="collapse{{ $faq->id }}">
                                                        <div class="d-flex align-items-center w-100">
                                                            <span class="badge bg-label-{{ $faq->category->color }} me-3">
                                                                {{ $faq->category->name }}
                                                            </span>
                                                            <span class="flex-grow-1">{{ $faq->question }}</span>
                                                            <small class="text-muted me-3">
                                                                <i class="ti ti-eye me-1"></i>{{ $faq->views_count }}
                                                            </small>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse{{ $faq->id }}" 
                                                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                                     aria-labelledby="heading{{ $faq->id }}" 
                                                     data-bs-parent="#faqAccordion">
                                                    <div class="accordion-body">
                                                        <div class="mb-3">
                                                            {!! nl2br(e($faq->answer)) !!}
                                                        </div>
                                                        
                                                        <!-- Helpfulness Rating -->
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-muted">{{ __('master.was_this_helpful') }}?</small>
                                                                <div class="btn-group btn-group-sm ms-2" role="group">
                                                                    <button type="button" class="btn btn-outline-success" 
                                                                            onclick="markHelpful({{ $faq->id }})">
                                                                        <i class="ti ti-thumb-up"></i> {{ __('master.yes') }}
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-danger" 
                                                                            onclick="markNotHelpful({{ $faq->id }})">
                                                                        <i class="ti ti-thumb-down"></i> {{ __('master.no') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="text-end">
                                                                <small class="text-muted">
                                                                    {{ $faq->helpful_count }} {{ __('master.helpful') }} / 
                                                                    {{ $faq->not_helpful_count }} {{ __('master.not_helpful') }}
                                                                    @if($faq->helpfulness_percentage > 0)
                                                                        ({{ $faq->helpfulness_percentage }}%)
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $faqs->appends(request()->query())->links() }}
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="ti ti-help-circle text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="text-muted mt-3">{{ __('master.no_faqs_found') }}</h5>
                                        <p class="text-muted">{{ __('master.try_different_search') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function markHelpful(faqId) {
            fetch(`{{ url('laboratory/faq') }}/${faqId}/helpful`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the helpful count display
                    location.reload(); // Simple refresh for now
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function markNotHelpful(faqId) {
            fetch(`{{ url('laboratory/faq') }}/${faqId}/not-helpful`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the not helpful count display
                    location.reload(); // Simple refresh for now
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
    @endpush
</x-app-layout>



