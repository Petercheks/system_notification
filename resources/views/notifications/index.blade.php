@extends('layouts.app')

@section('title', 'Notifications · Dashboard')

@section('content')
<div
    x-data="notificationDashboard()"
    x-init="init()"
    class="grid grid-cols-1 lg:grid-cols-6 gap-6 items-start lg:items-stretch lg:flex-1 lg:min-h-0"
>
    {{-- Send form --}}
    <section class="lg:col-span-2 lg:self-start bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-900">Send a notification</h2>
            <p class="text-sm text-slate-500 mt-1">
                Pick a category and write a message. Subscribed users will receive it through their preferred channels.
            </p>
        </div>

        <form @submit.prevent="submit()" class="space-y-4">
            {{-- Category --}}
            <div>
                <label for="category_slug" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Category <span class="text-rose-500">*</span>
                </label>
                <select
                    id="category_slug"
                    x-model="form.category_slug"
                    :disabled="loadingCategories || submitting"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed"
                    :class="{ 'border-rose-400 ring-rose-200': errors.category_slug }"
                >
                    <option value="" disabled>
                        <span>Select a category</span>
                    </option>
                    <template x-for="category in categories" :key="category.slug">
                        <option :value="category.slug" x-text="category.name"></option>
                    </template>
                </select>
                <p x-show="errors.category_slug" x-text="errors.category_slug" class="mt-1.5 text-xs text-rose-600"></p>
            </div>

            {{-- Body --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="body" class="block text-sm font-medium text-slate-700">
                        Message <span class="text-rose-500">*</span>
                    </label>
                    <span
                        class="text-xs"
                        :class="form.body.length > 1000 ? 'text-rose-600 font-medium' : 'text-slate-400'"
                        x-text="`${form.body.length} / 1000`"
                    ></span>
                </div>
                <textarea
                    id="body"
                    rows="5"
                    x-model="form.body"
                    :disabled="submitting"
                    placeholder="What's the news?"
                    maxlength="1000"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm resize-none disabled:bg-slate-100 disabled:cursor-not-allowed"
                    :class="{ 'border-rose-400 ring-rose-200': errors.body }"
                ></textarea>
                <p x-show="errors.body" x-text="errors.body" class="mt-1.5 text-xs text-rose-600"></p>
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                :disabled="!canSubmit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg x-show="submitting" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                    <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-show="!submitting">Send notification</span>
                <span x-show="submitting">Dispatching…</span>
            </button>

            {{-- Feedback toast --}}
            <template x-if="feedback">
                <div
                    class="rounded-lg px-3 py-2.5 text-sm border"
                    :class="feedback.type === 'success'
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                        : 'bg-rose-50 border-rose-200 text-rose-800'"
                >
                    <p x-text="feedback.message"></p>
                </div>
            </template>
        </form>
    </section>

    {{-- Log history --}}
    <section class="lg:col-span-4 lg:h-full lg:min-h-0 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        {{-- Header + filters --}}
        <div class="shrink-0 px-6 py-5 border-b border-slate-200">
            <div class="flex items-start sm:items-center justify-between gap-4 flex-col sm:flex-row">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Notification log</h2>
                    <p class="text-sm text-slate-500 mt-1">
                        <span x-text="filteredNotifications.length"></span>
                        <span x-show="filteredNotifications.length !== notifications.length">
                            of <span x-text="notifications.length"></span>
                        </span>
                        records · newest first
                    </p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-4">
                <select
                    x-model="filters.category_slug"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                >
                    <option value="">All categories</option>
                    <template x-for="category in categories" :key="category.slug">
                        <option :value="category.slug" x-text="category.name"></option>
                    </template>
                </select>
                <select
                    x-model="filters.channel_slug"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                >
                    <option value="">All channels</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="push-notification">Push</option>
                </select>
                <select
                    x-model="filters.status"
                    class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs"
                >
                    <option value="">All status</option>
                    <option value="pending">Pending</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
        </div>

        {{-- Body: empty / loading / table — fills available height and scrolls internally --}}
        <div class="flex-1 min-h-0 overflow-y-auto">
            {{-- Empty state --}}
            <template x-if="!loadingNotifications && filteredNotifications.length === 0">
                <div class="h-full flex flex-col items-center justify-center px-6 py-16 text-center">
                    <div class="h-12 w-12 rounded-full bg-slate-100 grid place-items-center">
                        <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-medium text-slate-900">No notifications yet</p>
                    <p class="text-xs text-slate-500 mt-1">Send a message to see the log populate.</p>
                </div>
            </template>

            {{-- Loading state --}}
            <template x-if="loadingNotifications && notifications.length === 0">
                <div class="h-full flex flex-col items-center justify-center px-6 py-16 text-center">
                    <div class="h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-indigo-600"></div>
                    <p class="mt-3 text-sm text-slate-500">Loading notifications…</p>
                </div>
            </template>

            {{-- Table --}}
            <table x-show="filteredNotifications.length > 0" class="w-full table-fixed divide-y divide-slate-200">
                <colgroup>
                    <col class="w-[15%]">
                    <col class="w-[18%]">
                    <col class="w-[10%]">
                    <col class="w-[16%]">
                    <col class="w-[13%]">
                    <col class="w-[28%]">
                </colgroup>
                <thead class="bg-slate-50 sticky top-0 z-10 shadow-[0_1px_0_0_rgb(226_232_240)]">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">When</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Channel</th>
                        <th class="px-2 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <template x-for="n in filteredNotifications" :key="n.id">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-3 py-3.5 text-xs text-slate-500 truncate" :title="formatDate(n.created_at)" x-text="formatDate(n.created_at)"></td>
                            <td class="px-3 py-3.5 text-sm text-slate-700 truncate" :title="n.user_name || `User #${n.user_id}`" x-text="n.user_name || `User #${n.user_id}`"></td>
                            <td class="px-3 py-3.5">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset max-w-full truncate"
                                    :class="categoryClasses(n.category_slug)"
                                    :title="formatSlug(n.category_slug)"
                                    x-text="formatSlug(n.category_slug)"
                                ></span>
                            </td>
                            <td class="px-3 py-3.5 text-sm text-slate-700 truncate" :title="formatSlug(n.channel_slug)" x-text="formatSlug(n.channel_slug)"></td>
                            <td class="px-2 py-3.5">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset whitespace-nowrap"
                                    :class="statusClasses(n.status)"
                                    :title="formatSlug(n.status)"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full shrink-0" :class="statusDotClasses(n.status)"></span>
                                    <span x-text="formatSlug(n.status)"></span>
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-sm text-slate-700">
                                <div class="truncate" :title="n.message_body" x-text="n.message_body"></div>
                                <template x-if="n.error_message">
                                    <div class="mt-1 text-xs text-rose-600 truncate" :title="n.error_message">
                                        ⚠ <span x-text="n.error_message"></span>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    function notificationDashboard() {
        return {
            categories: [],
            notifications: [],
            loadingCategories: false,
            loadingNotifications: false,
            submitting: false,
            feedback: null,
            errors: {},
            form: {
                category_slug: '',
                body: '',
            },
            filters: {
                category_slug: '',
                channel_slug: '',
                status: '',
            },

            get canSubmit() {
                return !this.submitting
                    && this.form.category_slug !== ''
                    && this.form.body.trim().length > 0
                    && this.form.body.length <= 1000;
            },

            get filteredNotifications() {
                return this.notifications.filter(n => {
                    if (this.filters.category_slug && n.category_slug !== this.filters.category_slug) return false;
                    if (this.filters.channel_slug && n.channel_slug !== this.filters.channel_slug) return false;
                    if (this.filters.status && n.status !== this.filters.status) return false;
                    return true;
                });
            },

            async init() {
                await Promise.all([this.loadCategories(), this.refresh()]);
            },

            async loadCategories() {
                this.loadingCategories = true;
                try {
                    const res = await fetch('/api/v1/categories', {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const json = await res.json();
                    this.categories = json.data ?? [];
                } catch (e) {
                    this.feedback = { type: 'error', message: 'Could not load categories.' };
                } finally {
                    this.loadingCategories = false;
                }
            },

            async refresh() {
                this.loadingNotifications = true;
                try {
                    const res = await fetch('/api/v1/notifications', {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const json = await res.json();
                    this.notifications = json.data ?? [];
                } catch (e) {
                    this.feedback = { type: 'error', message: 'Could not load notifications.' };
                } finally {
                    this.loadingNotifications = false;
                }
            },

            async submit() {
                if (!this.canSubmit) return;

                this.submitting = true;
                this.errors = {};
                this.feedback = null;

                try {
                    const res = await fetch('/api/v1/messages', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                        body: JSON.stringify({
                            category_slug: this.form.category_slug,
                            body: this.form.body,
                        }),
                    });

                    if (res.status === 422) {
                        const json = await res.json();
                        const fieldErrors = json.errors ?? {};
                        this.errors = Object.fromEntries(
                            Object.entries(fieldErrors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                        );
                        this.feedback = { type: 'error', message: json.message ?? 'Validation failed.' };
                        return;
                    }

                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    this.feedback = { type: 'success', message: 'Message queued. Notifications are being dispatched.' };
                    this.form.body = '';
                    await this.refresh();
                } catch (e) {
                    this.feedback = { type: 'error', message: 'Something went wrong while sending the message.' };
                } finally {
                    this.submitting = false;
                }
            },

            formatDate(iso) {
                if (!iso) return '—';
                const d = new Date(iso);
                if (Number.isNaN(d.getTime())) return iso;
                const day = d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                const time = d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: false });
                return `${day} · ${time}`;
            },

            formatSlug(slug) {
                if (!slug) return '—';
                return slug.charAt(0).toUpperCase() + slug.slice(1).replace(/[-_]/g, ' ');
            },

            statusClasses(status) {
                switch (status) {
                    case 'delivered': return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
                    case 'failed':    return 'bg-rose-50 text-rose-700 ring-rose-200';
                    case 'pending':   return 'bg-amber-50 text-amber-700 ring-amber-200';
                    default:          return 'bg-slate-50 text-slate-700 ring-slate-200';
                }
            },

            statusDotClasses(status) {
                switch (status) {
                    case 'delivered': return 'bg-emerald-500';
                    case 'failed':    return 'bg-rose-500';
                    case 'pending':   return 'bg-amber-500';
                    default:          return 'bg-slate-400';
                }
            },

            categoryClasses(slug) {
                switch (slug) {
                    case 'sports':  return 'bg-orange-50 text-orange-700 ring-orange-200';
                    case 'finance': return 'bg-sky-50 text-sky-700 ring-sky-200';
                    case 'movies':  return 'bg-violet-50 text-violet-700 ring-violet-200';
                    default:        return 'bg-slate-50 text-slate-700 ring-slate-200';
                }
            },
        };
    }
</script>
@endsection
