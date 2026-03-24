<x-filament-widgets::widget class="fi-wi-calendar-js">
    <x-filament::section>
        <div
            x-data="calendarJsWidget({
                defaultView: @js($calendarView),
                initialDate: @js(now()->toDateString()),
                dayStart: @js((string) config('booking.day_start', '08:00')),
                dayEnd: @js((string) config('booking.day_end', '22:00')),
            })"
            x-init="init()"
            x-on:calendarjs-refresh.window="refreshCalendar()"
            class="calendarjs-shell"
        >
            <div class="calendarjs-toolbar">
                <div class="calendarjs-toolbar-group">
                    <x-filament::button color="gray" size="sm" x-on:click="prev()">Prev</x-filament::button>
                    <x-filament::button color="gray" size="sm" x-on:click="today()">Today</x-filament::button>
                    <x-filament::button color="gray" size="sm" x-on:click="next()">Next</x-filament::button>
                </div>

                <div class="calendarjs-toolbar-heading" x-text="heading"></div>

                <div class="calendarjs-toolbar-group">
                    <x-filament::button color="gray" size="sm" x-on:click="setView('day')">Day</x-filament::button>
                    <x-filament::button color="gray" size="sm" x-on:click="setView('week')">Week</x-filament::button>
                    <x-filament::button color="gray" size="sm" x-on:click="openCreateLeave()">Create Leave</x-filament::button>
                    <x-filament::button color="gray" size="sm" x-on:click="openCreateDayOff()">Create Day Off</x-filament::button>
                </div>
            </div>

            <div class="calendarjs-host" wire:ignore>
                <div x-ref="calendar" class="calendarjs-calendar"></div>
            </div>
        </div>
    </x-filament::section>

    <x-filament-actions::modals />

    <script>
        (() => {
            if (window.calendarJsWidget) {
                return;
            }

            const toDateInput = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            const toMonthDay = (date) => date.toLocaleDateString(undefined, {
                month: 'short',
                day: 'numeric',
            });

            const loadScript = (id, src) => {
                if (document.getElementById(id)) {
                    return Promise.resolve();
                }

                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.id = id;
                    script.src = src;
                    script.async = true;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            };

            const loadStyle = (id, href) => {
                if (document.getElementById(id)) {
                    return;
                }

                const link = document.createElement('link');
                link.id = id;
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            };

            window.calendarJsWidget = ({ defaultView, initialDate, dayStart, dayEnd }) => ({
                schedule: null,
                heading: '',
                view: defaultView || 'week',
                anchorDate: new Date(initialDate || toDateInput(new Date())),
                dayStart,
                dayEnd,
                lastRefreshToken: null,

                async init() {
                    await this.loadAssets();
                    await this.refreshCalendar();
                },

                async loadAssets() {
                    loadStyle('calendarjs-material-icons', 'https://fonts.googleapis.com/css?family=Material+Icons');
                    loadStyle('calendarjs-ce-style', 'https://cdn.jsdelivr.net/npm/@calendarjs/ce/dist/style.min.css');

                    if (!window.lemonade) {
                        await loadScript('calendarjs-lemonade', 'https://cdn.jsdelivr.net/npm/lemonadejs/dist/lemonade.min.js');
                    }

                    if (!window.calendarjs) {
                        await loadScript('calendarjs-ce', 'https://cdn.jsdelivr.net/npm/@calendarjs/ce/dist/index.min.js');
                    }
                },

                range() {
                    const start = new Date(this.anchorDate);
                    const end = new Date(this.anchorDate);

                    if (this.view === 'day') {
                        return {
                            start: toDateInput(start),
                            end: toDateInput(end),
                        };
                    }

                    const startDay = start.getDay();
                    start.setDate(start.getDate() - startDay);
                    end.setTime(start.getTime());
                    end.setDate(start.getDate() + 6);

                    return {
                        start: toDateInput(start),
                        end: toDateInput(end),
                    };
                },

                updateHeading() {
                    if (this.view === 'day') {
                        this.heading = this.anchorDate.toLocaleDateString(undefined, {
                            weekday: 'long',
                            month: 'long',
                            day: 'numeric',
                            year: 'numeric',
                        });

                        return;
                    }

                    const { start, end } = this.range();
                    const startDate = new Date(start);
                    const endDate = new Date(end);
                    this.heading = `${toMonthDay(startDate)} - ${toMonthDay(endDate)}`;
                },

                async refreshCalendar() {
                    if (!window.calendarjs || !this.$refs.calendar) {
                        return;
                    }

                    const token = `${Date.now()}-${Math.random()}`;
                    this.lastRefreshToken = token;

                    const { start, end } = this.range();
                    const events = await this.$wire.fetchCalendarEvents(start, end);

                    if (this.lastRefreshToken !== token) {
                        return;
                    }

                    this.$refs.calendar.innerHTML = '';
                    this.updateHeading();

                    const callbacks = {
                        oncreate: async (_self, eventsCreated) => {
                            const event = Array.isArray(eventsCreated) ? eventsCreated[0] : eventsCreated;

                            if (!event) {
                                await this.refreshCalendar();
                                return;
                            }

                            await this.$wire.openCreateBookingFromCalendar(this.toPayload(event));
                            await this.refreshCalendar();
                        },
                        ondblclick: async (_self, event) => {
                            const guid = event?.recordGuid ?? event?.guid;

                            if (!guid) {
                                return;
                            }

                            await this.$wire.openEditActionForCalendarEvent(String(guid));
                        },
                        onchangeevent: async (_self, newValue, oldValue) => {
                            const result = await this.$wire.persistCalendarEventChange(
                                this.toPayload(newValue),
                                this.toPayload(oldValue || {})
                            );

                            if (!result?.ok) {
                                if (result?.message) {
                                    window.alert(result.message);
                                }

                                await this.refreshCalendar();
                                return;
                            }

                            await this.refreshCalendar();
                        },
                        ondelete: async (_self, guid) => {
                            if (!guid) {
                                await this.refreshCalendar();
                                return;
                            }

                            await this.$wire.requestDeleteForCalendarEvent(String(guid));
                            await this.refreshCalendar();
                        },
                    };

                    const options = {
                        type: this.view,
                        value: toDateInput(this.anchorDate),
                        data: events,
                        validRange: [this.dayStart, this.dayEnd],
                        ...callbacks,
                    };

                    this.schedule = window.calendarjs.Schedule(this.$refs.calendar, options);
                },

                toPayload(event) {
                    return JSON.parse(JSON.stringify(event || {}));
                },

                async setView(view) {
                    this.view = view;
                    await this.refreshCalendar();
                },

                async prev() {
                    const step = this.view === 'day' ? 1 : 7;
                    this.anchorDate.setDate(this.anchorDate.getDate() - step);
                    await this.refreshCalendar();
                },

                async next() {
                    const step = this.view === 'day' ? 1 : 7;
                    this.anchorDate.setDate(this.anchorDate.getDate() + step);
                    await this.refreshCalendar();
                },

                async today() {
                    this.anchorDate = new Date();
                    await this.refreshCalendar();
                },

                async openCreateLeave() {
                    await this.$wire.mountAction('createTherapistLeave', {
                        date: toDateInput(this.anchorDate),
                    });
                },

                async openCreateDayOff() {
                    await this.$wire.mountAction('createDayOff', {
                        date: toDateInput(this.anchorDate),
                    });
                },
            });
        })();
    </script>
</x-filament-widgets::widget>
