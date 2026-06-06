<x-app-layout>
    <x-slot name="title">{{ __('master.messages') }}</x-slot>

    @push('styles')
    <style>
        .msgr { display: flex; height: calc(100vh - 200px); min-height: 480px; border-radius: .75rem; overflow: hidden; box-shadow: 0 2px 14px rgba(15,23,42,.06); background: #fff; }
        .msgr-list { width: 340px; flex: 0 0 340px; border-right: 1px solid #eef0f2; display: flex; flex-direction: column; background: #fff; }
        .msgr-list-head { padding: 1rem; border-bottom: 1px solid #eef0f2; }
        .msgr-search { position: relative; }
        .msgr-search input { border-radius: 999px; padding-left: 2.4rem; }
        .msgr-search i { position: absolute; top: 50%; left: .9rem; transform: translateY(-50%); color: #98a2b3; }
        .msgr-convs { overflow-y: auto; flex: 1; }
        .msgr-conv { display: flex; gap: .7rem; padding: .8rem 1rem; cursor: pointer; border-bottom: 1px solid #f4f6f8; align-items: center; }
        .msgr-conv:hover { background: #f7f9fb; }
        .msgr-conv.active { background: rgba(1,185,198,.08); }
        .msgr-conv .av { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; background: #e9eef3; flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; color: #01b9c6; }
        .msgr-conv .cv-name { font-weight: 600; color: #0f172a; font-size: .9rem; }
        .msgr-conv .cv-case { font-size: .72rem; color: #01b9c6; font-weight: 600; }
        .msgr-conv .cv-last { font-size: .8rem; color: #8a909d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
        .msgr-conv .cv-meta { margin-left: auto; text-align: right; flex: 0 0 auto; }
        .msgr-conv .cv-time { font-size: .68rem; color: #b6bdc7; }
        .msgr-conv .cv-unread { display: inline-block; min-width: 18px; height: 18px; line-height: 18px; text-align: center; font-size: .68rem; color: #fff; background: #01b9c6; border-radius: 999px; padding: 0 5px; margin-top: .2rem; }

        .msgr-thread { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .msgr-thread-head { padding: .85rem 1.1rem; border-bottom: 1px solid #eef0f2; display: flex; align-items: center; gap: .7rem; }
        .msgr-thread-head .av { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: #e9eef3; display: inline-flex; align-items: center; justify-content: center; color: #01b9c6; }
        .msgr-body { flex: 1; overflow-y: auto; padding: 1rem 1.2rem; background: #f7f9fb; }
        .msgr-msg { display: flex; gap: .55rem; margin-bottom: .85rem; max-width: 78%; }
        .msgr-msg .b { background: #fff; border: 1px solid #e9eef3; border-radius: .9rem; padding: .5rem .8rem; }
        .msgr-msg .nm { font-size: .7rem; font-weight: 600; color: #01b9c6; margin-bottom: .1rem; }
        .msgr-msg .tx { font-size: .9rem; color: #0f172a; white-space: pre-wrap; word-break: break-word; }
        .msgr-msg .tm { font-size: .66rem; color: #b6bdc7; margin-top: .15rem; }
        .msgr-msg.mine { margin-left: auto; flex-direction: row-reverse; }
        .msgr-msg.mine .b { background: #01b9c6; border-color: #01b9c6; }
        .msgr-msg.mine .nm { color: rgba(255,255,255,.85); }
        .msgr-msg.mine .tx { color: #fff; }
        .msgr-msg.mine .tm { color: rgba(255,255,255,.85); text-align: right; }
        .msgr-foot { padding: .8rem 1rem; border-top: 1px solid #eef0f2; display: flex; gap: .6rem; align-items: flex-end; }
        .msgr-foot textarea { resize: none; }
        .msgr-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #98a2b3; text-align: center; padding: 2rem; }

        @media (max-width: 991.98px) {
            .msgr { height: auto; flex-direction: column; }
            .msgr-list { width: 100%; flex: none; border-right: 0; border-bottom: 1px solid #eef0f2; max-height: 320px; }
            .msgr-thread { min-height: 420px; }
        }
    </style>
    @endpush

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex align-items-center mb-4">
            <h4 class="mb-0"><i class="icon-base ti tabler-brand-messenger me-2 text-primary"></i>{{ __('master.messages') }}</h4>
        </div>

        @if(empty($conversations))
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="icon-base ti tabler-message-2-off" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">{{ __('master.no_conversations_yet') }}</p>
                    <small>{{ __('master.start_chat_from_case') }}</small>
                </div>
            </div>
        @else
        <div class="msgr">
            <div class="msgr-list">
                <div class="msgr-list-head">
                    <div class="msgr-search">
                        <i class="icon-base ti tabler-search"></i>
                        <input type="text" class="form-control" id="msgrSearch" placeholder="{{ __('master.search') }}...">
                    </div>
                </div>
                <div class="msgr-convs" id="msgrConvs">
                    @foreach($conversations as $c)
                    <div class="msgr-conv" data-case="{{ $c['case_id'] }}" data-channel="{{ $c['channel'] }}"
                         data-name="{{ $c['name'] }}" data-avatar="{{ $c['avatar'] }}"
                         data-caselabel="{{ $c['case_label'] }}" data-caseurl="{{ $c['case_url'] }}"
                         data-search="{{ strtolower($c['name'] . ' ' . $c['case_label'] . ' ' . $c['patient']) }}">
                        @if($c['avatar'])
                            <img class="av" src="{{ $c['avatar'] }}" alt="">
                        @else
                            <span class="av"><i class="icon-base ti tabler-user"></i></span>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <div class="cv-name">{{ $c['name'] }}</div>
                            <div class="cv-case">{{ $c['counterpart_label'] }} &middot; #{{ $c['case_label'] }}</div>
                            <div class="cv-last">{{ $c['last'] }}</div>
                        </div>
                        <div class="cv-meta">
                            <div class="cv-time">{{ $c['last_time'] }}</div>
                            @if($c['unread'] > 0)
                                <span class="cv-unread" data-unread>{{ $c['unread'] }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="msgr-thread">
                <div class="msgr-thread-head d-none" id="msgrHead">
                    <span class="av" id="msgrHeadAv"><i class="icon-base ti tabler-user"></i></span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold" id="msgrHeadName"></div>
                        <a href="#" target="_blank" class="small text-primary" id="msgrHeadCase"></a>
                    </div>
                </div>
                <div class="msgr-body" id="msgrBody">
                    <div class="msgr-empty">
                        <i class="icon-base ti tabler-message-circle" style="font-size: 2.6rem;"></i>
                        <p class="mt-2 mb-0">{{ __('master.select_a_conversation') }}</p>
                    </div>
                </div>
                <form class="msgr-foot d-none" id="msgrForm">
                    <textarea class="form-control" id="msgrInput" rows="1" placeholder="{{ __('master.type_a_message') }}" maxlength="2000"></textarea>
                    <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-send"></i></button>
                </form>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
    (function () {
        const convs = Array.from(document.querySelectorAll('.msgr-conv'));
        const body = document.getElementById('msgrBody');
        const form = document.getElementById('msgrForm');
        const input = document.getElementById('msgrInput');
        const head = document.getElementById('msgrHead');
        const headName = document.getElementById('msgrHeadName');
        const headAv = document.getElementById('msgrHeadAv');
        const headCase = document.getElementById('msgrHeadCase');
        const search = document.getElementById('msgrSearch');
        if (!convs.length || !body) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const base = "{{ url('/case-chat') }}";
        const labels = { empty: @json(__('master.no_messages_yet')), you: @json(__('master.you')) };

        let active = null;
        let pollTimer = null;

        function esc(s){ const d=document.createElement('div'); d.textContent = s==null?'':String(s); return d.innerHTML; }

        function render(messages) {
            if (!messages.length) { body.innerHTML = '<div class="msgr-empty"><p class="mb-0">'+labels.empty+'</p></div>'; return; }
            body.innerHTML = messages.map(function (m) {
                const av = m.avatar ? '<img class="av" style="width:32px;height:32px;border-radius:50%;object-fit:cover" src="'+esc(m.avatar)+'">'
                    : '<span class="av" style="width:32px;height:32px;border-radius:50%;background:#e9eef3;display:inline-flex;align-items:center;justify-content:center;color:#01b9c6;flex:0 0 auto"><i class="icon-base ti tabler-user"></i></span>';
                return '<div class="msgr-msg '+(m.mine?'mine':'')+'">'+av+
                    '<div><div class="b"><div class="nm">'+esc(m.mine?labels.you:m.sender)+'</div><div class="tx">'+esc(m.body)+'</div></div>'+
                    '<div class="tm">'+esc(m.time)+'</div></div></div>';
            }).join('');
            body.scrollTop = body.scrollHeight;
        }

        function urlFor(el){ return base + '/' + el.getAttribute('data-case') + '/' + el.getAttribute('data-channel'); }

        function loadMessages() {
            if (!active) return;
            fetch(urlFor(active), { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } })
                .then(r => r.json()).then(d => render(d.messages || [])).catch(()=>{});
        }

        function openConv(el) {
            convs.forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            active = el;
            // clear unread badge
            const u = el.querySelector('[data-unread]'); if (u) u.remove();
            // header
            head.classList.remove('d-none');
            form.classList.remove('d-none');
            headName.textContent = el.getAttribute('data-name');
            const avUrl = el.getAttribute('data-avatar');
            headAv.innerHTML = avUrl ? '<img src="'+avUrl+'" style="width:40px;height:40px;border-radius:50%;object-fit:cover">' : '<i class="icon-base ti tabler-user"></i>';
            const caseUrl = el.getAttribute('data-caseurl');
            headCase.textContent = '#' + el.getAttribute('data-caselabel');
            if (caseUrl) headCase.setAttribute('href', caseUrl);
            body.innerHTML = '<div class="msgr-empty"><span class="spinner-border spinner-border-sm"></span></div>';
            loadMessages();
            if (pollTimer) clearInterval(pollTimer);
            pollTimer = setInterval(loadMessages, 12000);
        }

        convs.forEach(el => el.addEventListener('click', () => openConv(el)));

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!active) return;
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            fetch(urlFor(active), {
                method:'POST',
                headers:{ 'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':token },
                body: JSON.stringify({ body: text })
            }).then(r=>r.json()).then(()=>loadMessages()).catch(()=>{});
        });

        input && input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.dispatchEvent(new Event('submit')); }
        });

        search && search.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            convs.forEach(el => {
                el.style.display = (!q || el.getAttribute('data-search').includes(q)) ? '' : 'none';
            });
        });

        // Auto-open first conversation on desktop
        if (window.innerWidth >= 992 && convs[0]) openConv(convs[0]);
    })();
    </script>
    @endpush
</x-app-layout>
