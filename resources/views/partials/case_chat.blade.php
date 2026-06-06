@php
    $chatUser = auth()->user();
    $chatRole = optional($chatUser->role)->name;
    $myChannels = \App\Models\CaseMessage::channelsForRole($chatRole ?? '');

    $visibleChannels = [];
    foreach ($myChannels as $ch) {
        $counterpart = \App\Models\CaseMessage::counterpartRole($ch, $chatRole);

        // Counterpart (other than admin) must be assigned for the channel to be usable
        if ($counterpart && $counterpart !== 'admin') {
            if (!\App\Models\CaseMessage::userIdForRole($case, $counterpart)) {
                continue;
            }
        }
        // Non-admin viewer must be the assigned participant for their role
        if ($chatRole !== 'admin') {
            $mine = \App\Models\CaseMessage::userIdForRole($case, $chatRole);
            if (!$mine || (int) $mine !== (int) $chatUser->id) {
                continue;
            }
        }

        $unread = \App\Models\CaseMessage::where('case_id', $case->id)
            ->where('channel', $ch)
            ->where('sender_id', '!=', $chatUser->id)
            ->whereNull('read_at')
            ->count();

        $visibleChannels[$ch] = [
            'counterpart' => $counterpart,
            'label'       => __('master.role_' . $counterpart),
            'unread'      => $unread,
        ];
    }
@endphp

@if(!empty($visibleChannels))
<div class="card case-chat-card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">
                <i class="icon-base ti tabler-messages me-2 text-primary"></i>{{ __('master.case_chat') }}
            </h5>
        </div>

        <div class="card-body">
            <ul class="nav nav-pills flex-wrap gap-2 mb-3" id="caseChatTabs" role="tablist">
                @foreach($visibleChannels as $ch => $info)
                <li class="nav-item">
                    <button class="btn btn-sm {{ $loop->first ? 'btn-primary' : 'btn-outline-primary' }} case-chat-tab position-relative"
                            data-channel="{{ $ch }}" type="button">
                        <i class="icon-base ti tabler-user me-1"></i>{{ $info['label'] }}
                        <span class="badge bg-danger rounded-pill case-chat-unread {{ $info['unread'] ? '' : 'd-none' }}"
                              data-channel-badge="{{ $ch }}">{{ $info['unread'] }}</span>
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="case-chat-messages" id="caseChatMessages">
                <div class="text-center text-muted py-4">
                    <span class="spinner-border spinner-border-sm me-2"></span>{{ __('master.loading') }}
                </div>
            </div>

            <form id="caseChatForm" class="mt-3 d-flex gap-2 align-items-end">
                <textarea class="form-control" id="caseChatInput" rows="1"
                          placeholder="{{ __('master.type_a_message') }}" maxlength="2000"></textarea>
                <button type="submit" class="btn btn-primary" id="caseChatSend">
                    <i class="icon-base ti tabler-send"></i>
                </button>
            </form>
        </div>
</div>

@push('styles')
<style>
    .case-chat-messages { height: 340px; overflow-y: auto; padding: .5rem; background: #f7f9fb; border-radius: .5rem; border: 1px solid #eef0f2; }
    .case-chat-msg { display: flex; gap: .6rem; margin-bottom: .9rem; max-width: 82%; }
    .case-chat-msg .cc-avatar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; flex: 0 0 auto; background: #e9eef3; }
    .case-chat-msg .cc-bubble { background: #fff; border: 1px solid #e9eef3; border-radius: .85rem; padding: .5rem .8rem; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .case-chat-msg .cc-name { font-size: .72rem; font-weight: 600; color: #01b9c6; margin-bottom: .1rem; }
    .case-chat-msg .cc-text { font-size: .9rem; color: #0f172a; white-space: pre-wrap; word-break: break-word; }
    .case-chat-msg .cc-time { font-size: .68rem; color: #98a2b3; margin-top: .15rem; }
    .case-chat-msg.cc-mine { margin-left: auto; flex-direction: row-reverse; }
    .case-chat-msg.cc-mine .cc-bubble { background: #01b9c6; border-color: #01b9c6; }
    .case-chat-msg.cc-mine .cc-name { color: rgba(255,255,255,.85); }
    .case-chat-msg.cc-mine .cc-text { color: #fff; }
    .case-chat-msg.cc-mine .cc-time { color: rgba(255,255,255,.8); text-align: right; }
    .case-chat-empty { text-align: center; color: #98a2b3; padding: 2rem 0; font-size: .9rem; }
    #caseChatInput { resize: none; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const wrap = document.getElementById('caseChatMessages');
    const form = document.getElementById('caseChatForm');
    const input = document.getElementById('caseChatInput');
    const tabs = Array.from(document.querySelectorAll('.case-chat-tab'));
    if (!wrap || !form || !input || !tabs.length) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const baseMessages = "{{ route('case.chat.messages', ['case' => $case->id, 'channel' => '__CH__']) }}";
    const baseSend = "{{ route('case.chat.send', ['case' => $case->id, 'channel' => '__CH__']) }}";
    const labels = {
        empty: @json(__('master.no_messages_yet')),
        you: @json(__('master.you')),
    };

    let activeChannel = tabs[0].getAttribute('data-channel');
    let pollTimer = null;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function renderMessages(messages) {
        if (!messages.length) {
            wrap.innerHTML = '<div class="case-chat-empty">' + labels.empty + '</div>';
            return;
        }
        wrap.innerHTML = messages.map(function (m) {
            const avatar = m.avatar
                ? '<img class="cc-avatar" src="' + esc(m.avatar) + '" alt="">'
                : '<span class="cc-avatar d-inline-flex align-items-center justify-content-center"><i class="icon-base ti tabler-user"></i></span>';
            return '<div class="case-chat-msg ' + (m.mine ? 'cc-mine' : '') + '">' +
                avatar +
                '<div>' +
                    '<div class="cc-bubble">' +
                        '<div class="cc-name">' + esc(m.mine ? labels.you : m.sender) + '</div>' +
                        '<div class="cc-text">' + esc(m.body) + '</div>' +
                    '</div>' +
                    '<div class="cc-time">' + esc(m.time) + '</div>' +
                '</div>' +
            '</div>';
        }).join('');
        wrap.scrollTop = wrap.scrollHeight;
    }

    function clearBadge(channel) {
        const badge = document.querySelector('[data-channel-badge="' + channel + '"]');
        if (badge) { badge.classList.add('d-none'); badge.textContent = '0'; }
    }

    function load(scroll) {
        fetch(baseMessages.replace('__CH__', activeChannel), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            renderMessages(data.messages || []);
            clearBadge(activeChannel);
        })
        .catch(function () {});
    }

    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(load, 12000);
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('btn-primary'); t.classList.add('btn-outline-primary'); });
            tab.classList.add('btn-primary'); tab.classList.remove('btn-outline-primary');
            activeChannel = tab.getAttribute('data-channel');
            wrap.innerHTML = '<div class="case-chat-empty"><span class="spinner-border spinner-border-sm me-2"></span></div>';
            load(true);
            startPolling();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const body = input.value.trim();
        if (!body) return;
        input.value = '';
        fetch(baseSend.replace('__CH__', activeChannel), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ body: body })
        })
        .then(function (r) { return r.json(); })
        .then(function () { load(true); })
        .catch(function () {});
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });

    load(true);
    startPolling();
})();
</script>
@endpush
@endif
