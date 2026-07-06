@extends('layout.blank')

@section('content')

<div class="form-container fadeIn">

    <a href="{{ secure_url(route('notes.index', [], false)) }}" class="btn-back">Voltar</a>

    <h1>Nova Nota</h1>
    <div class="title-underline"></div>

    <form action="{{ secure_url(route('notes.store', [], false)) }}" method="POST" autocomplete="off">
        @csrf

        <div class="form-group">
            <label for="title">Título da Nota <span class="required">*</span></label>
            <input type="text"
                   id="title"
                   name="title"
                   placeholder="Digite o título da nota"
                   required>
        </div>

        <div class="form-group">
            <label for="created_day_display">Dia da Criação <span class="required">*</span></label>

            <div class="calendar-field" id="calendarField">
                <button type="button" class="calendar-field-input" id="calendarFieldBtn">
                    <span id="calendarFieldText">Selecione uma data</span>
                    <span class="calendar-field-icon">📅</span>
                </button>

                <div class="calendar-popover" id="calendarPopover">
                    <header class="calendar-header">
                        <button type="button" class="calendar-nav-btn" id="calendarPrevBtn" aria-label="Mês anterior">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                        <span class="calendar-heading" id="calendarHeading"></span>
                        <button type="button" class="calendar-nav-btn" id="calendarNextBtn" aria-label="Próximo mês">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </header>

                    <div class="calendar-weekdays">
                        <span>D</span><span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span>
                    </div>

                    <div class="calendar-grid" id="calendarGrid"></div>
                </div>
            </div>

            <input type="hidden" id="created_day" name="created_day" required>
        </div>

        <button type="submit">Criar Nota</button>

    </form>

</div>

<script>
(function () {
    const field = document.getElementById('calendarField');
    const fieldBtn = document.getElementById('calendarFieldBtn');
    const fieldText = document.getElementById('calendarFieldText');
    const popover = document.getElementById('calendarPopover');
    const grid = document.getElementById('calendarGrid');
    const heading = document.getElementById('calendarHeading');
    const prevBtn = document.getElementById('calendarPrevBtn');
    const nextBtn = document.getElementById('calendarNextBtn');
    const hiddenInput = document.getElementById('created_day');

    const monthNames = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
    let selectedDate = null;

    function formatISO(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function formatDisplay(date) {
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        return `${d}/${m}/${y}`;
    }

    function renderCalendar() {
        heading.textContent = `${monthNames[viewDate.getMonth()]} ${viewDate.getFullYear()}`;
        grid.innerHTML = '';

        const firstDayOfMonth = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);
        const startWeekday = firstDayOfMonth.getDay();
        const daysInMonth = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 0).getDate();
        const daysInPrevMonth = new Date(viewDate.getFullYear(), viewDate.getMonth(), 0).getDate();

        const totalCells = 42;
        for (let i = 0; i < totalCells; i++) {
            const dayNum = i - startWeekday + 1;
            let cellDate, outOfMonth = false;

            if (dayNum < 1) {
                cellDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, daysInPrevMonth + dayNum);
                outOfMonth = true;
            } else if (dayNum > daysInMonth) {
                cellDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, dayNum - daysInMonth);
                outOfMonth = true;
            } else {
                cellDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), dayNum);
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'calendar-day';
            btn.textContent = cellDate.getDate();

            if (outOfMonth) btn.classList.add('outside');
            if (cellDate.getTime() === today.getTime()) btn.classList.add('today');
            if (selectedDate && cellDate.getTime() === selectedDate.getTime()) btn.classList.add('selected');

            btn.addEventListener('click', function () {
                selectedDate = cellDate;
                hiddenInput.value = formatISO(cellDate);
                fieldText.textContent = formatDisplay(cellDate);
                fieldText.classList.add('has-value');
                if (outOfMonth) {
                    viewDate = new Date(cellDate.getFullYear(), cellDate.getMonth(), 1);
                }
                closePopover();
                renderCalendar();
            });

            grid.appendChild(btn);
        }
    }

    function openPopover() {
        popover.classList.add('open');
        fieldBtn.setAttribute('aria-expanded', 'true');
    }

    function closePopover() {
        popover.classList.remove('open');
        fieldBtn.setAttribute('aria-expanded', 'false');
    }

    fieldBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (popover.classList.contains('open')) {
            closePopover();
        } else {
            openPopover();
        }
    });

    prevBtn.addEventListener('click', function () {
        viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1);
        renderCalendar();
    });

    nextBtn.addEventListener('click', function () {
        viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1);
        renderCalendar();
    });

    document.addEventListener('click', function (e) {
        if (!field.contains(e.target)) closePopover();
    });

    renderCalendar();
})();
</script>

@endsection