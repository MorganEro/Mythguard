class Calendar {
    constructor() {
        this.currentView = 'calendar';
        this.init();
    }

    init() {
        this.trigger = document.querySelector('.calendar-widget__trigger');
        this.content = document.querySelector('.calendar-widget__content');
        this.calendarView = document.querySelector('.calendar-view');
        this.listView = document.querySelector('.calendar-list-view');
        this.calendar = document.querySelector('#mythguard-calendar');
        this.calendarBtn = document.querySelector('.view-toggle__btn--calendar');
        this.listBtn = document.querySelector('.view-toggle__btn--list');

        if (!this.trigger || !this.content || !this.calendar) return;

        // State
        this.dates = {
            contracts: [],
            gatherings: []
        };

        const carets = this.content.querySelectorAll('.calendar-list-view__header .fas.fa-caret-up');
        carets.forEach(caret => {
            caret.addEventListener('click', () => this.toggleSection(caret));
        });

        this.flatpickr = flatpickr(this.calendar, this.getFlatpickrConfig());

        this.trigger.addEventListener('click', () => this.toggleCalendar());
        document.addEventListener('click', (e) => this.handleClickOutside(e));
        this.calendarBtn.addEventListener('click', () => this.switchView('calendar'));
        this.listBtn.addEventListener('click', () => this.switchView('list'));

        this.fetchDates();
    }

    getFlatpickrConfig() {
        const today = new Date();
        return {
            inline: true,
            dateFormat: 'Y-m-d',
            defaultDate: today,
            onChange: (_, dateStr) => this.handleDateSelect(dateStr),
            onDayCreate: (_, __, fp, dayElem) => {
                const dateStr = fp.formatDate(dayElem.dateObj, 'Y-m-d');
                const events = this.getEventsForDate(dateStr);

                if (events.length > 0) {
                    events.forEach(event => {
                        // Add base class for the event type
                        dayElem.classList.add(`has-${event.type}`);

                        // Add specific classes for contract start/end dates
                        if (event.type === 'contract') {
                            if (event.isStart) dayElem.classList.add('contract-start');
                            if (event.isEnd) dayElem.classList.add('contract-end');
                        }

                        // Add visual indicator
                        const dot = document.createElement('span');
                        dot.className = `event-dot ${event.type}-dot`;
                        if (event.isStart) dot.classList.add('start-dot');
                        if (event.isEnd) dot.classList.add('end-dot');
                        dayElem.appendChild(dot);
                    });
                    if (events.length > 1) dayElem.classList.add('has-multiple');
                }
            }
        };
    }


    async fetchDates() {
        try {
            const response = await wp.apiFetch({
                path: '/mythguard/v1/calendar'
            });
            this.dates = response;
            this.updateCalendarDates();
        } catch (error) {
            console.error('Error fetching calendar dates:', error);
        }
    }

    getEventsForDate(dateStr) {
        return [
            ...this.dates.contracts
                .filter(item => item.date === dateStr)
                .map(item => ({ ...item, type: 'contract' })),
            ...this.dates.gatherings
                .filter(item => item.date === dateStr)
                .map(item => ({ ...item, type: 'gathering' }))
        ];
    }

    updateCalendarDates() {
        if (!this.dates || !this.flatpickr) return;

        this.updateListView();

        const currentMonth = this.flatpickr.currentMonth;
        const currentYear = this.flatpickr.currentYear;
        if (this.flatpickr && typeof this.flatpickr.destroy === 'function') {
            this.flatpickr.destroy();
        }
        this.flatpickr = flatpickr(this.calendar, this.getFlatpickrConfig(new Date(currentYear, currentMonth)));
    }

    handleDateSelect(dateStr) {
        // Remove any existing events display
        const existingEvents = this.content.querySelector('.calendar-events');
        if (existingEvents) {
            existingEvents.remove();
        }

        const events = [
            ...this.dates.contracts.filter(item => item.date === dateStr),
            ...this.dates.gatherings.filter(item => item.date === dateStr)
        ];

        if (events.length > 0) {
            const eventList = events.map(event => `
                <div class="calendar-event calendar-event--${event.type}">
                    <span class="calendar-event__type">${event.type}</span>
                    <a href="${event.url}" class="calendar-event__title">${event.title}</a>
                </div>
            `).join('');

            this.content.querySelector('.calendar-legend').insertAdjacentHTML('afterend',
                `<div class="calendar-events">${eventList}</div>`);
        }
    }

    toggleCalendar() {
        const isHidden = this.content.style.display === 'none';
        this.content.style.display = isHidden ? 'block' : 'none';
        if (isHidden) this.fetchDates();
    }

    handleClickOutside(e) {
        if (!this.content.contains(e.target) &&
            !this.trigger.contains(e.target) &&
            this.content.style.display !== 'none') {
            this.content.style.display = 'none';
        }
    }

    switchView(view) {
        this.currentView = view;

        this.calendarBtn.classList.toggle('active', view === 'calendar');
        this.listBtn.classList.toggle('active', view === 'list');

        this.calendarView.classList.toggle('active', view === 'calendar');
        this.listView.classList.toggle('active', view === 'list');

        if (view === 'list') {
            this.updateListView();
        }
    }

    toggleSection(caret) {
        const section = caret.closest('div');
        const items = section.querySelector('.calendar-list-view__items');
        caret.classList.toggle('collapsed');
        caret.classList.toggle('fa-caret-up');
        caret.classList.toggle('fa-caret-down');
        items.classList.toggle('collapsed');

        const sectionId = section.classList[0];
        const isCollapsed = caret.classList.contains('collapsed');
        localStorage.setItem(`${sectionId}-collapsed`, isCollapsed);
    }

    restoreCollapsedState() {
        const sections = ['calendar-list-view__contracts', 'calendar-list-view__gatherings'];
        sections.forEach(sectionId => {
            const isCollapsed = localStorage.getItem(`${sectionId}-collapsed`) === 'true';
            if (isCollapsed) {
                const section = this.content.querySelector(`.${sectionId}`);
                const header = section.querySelector('.calendar-list-view__header');
                const items = section.querySelector('.calendar-list-view__items');
                header.classList.add('collapsed');
                items.classList.add('collapsed');
                section.classList.add('collapsed');
            }
        });
    }

    formatDate(dateStr) {
        const date = new Date(dateStr);
        return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;
    }

    updateListView() {
        if (!this.listView) return;

        const contractsList = this.listView.querySelector('.calendar-list-view__contracts .calendar-list-view__items');
        if (contractsList) {
            contractsList.innerHTML = '';
            const contractsById = this.dates.contracts.reduce((acc, contract) => {
                if (!acc[contract.url]) {
                    acc[contract.url] = {
                        title: contract.title,
                        url: contract.url,
                        start: null,
                        end: null
                    };
                }
                if (contract.isStart) acc[contract.url].start = contract.date;
                if (contract.isEnd) acc[contract.url].end = contract.date;
                return acc;
            }, {});

            Object.values(contractsById).forEach(contract => {
                if (contract.start && contract.end) {
                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = contract.url;
                    link.className = 'calendar-list-item';
                    link.innerHTML = `
                    <div class="calendar-list-item__date">
                        <span>${this.formatDate(contract.start)}</span>
                        <span>${this.formatDate(contract.end)}</span>
                    </div>
                    <span class="calendar-list-item__title">${contract.title}</span>
                    `;
                    li.appendChild(link);
                    contractsList.appendChild(li);
                }
            });
        }

        const gatheringsList = this.listView.querySelector('.calendar-list-view__gatherings .calendar-list-view__items');
        if (gatheringsList) {
            gatheringsList.innerHTML = '';
            this.dates.gatherings.forEach(gathering => {
                const li = document.createElement('li');
                const link = document.createElement('a');
                link.href = gathering.url;
                link.className = 'calendar-list-item';
                link.innerHTML = `
                    <span class="calendar-list-item__date">${this.formatDate(gathering.date)}</span>
                    <span class="calendar-list-item__title">${gathering.title}</span>
                `;
                li.appendChild(link);
                gatheringsList.appendChild(li);
            });
        }
    }

}

export default Calendar;
