import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import EmptyState from '@/Components/EmptyState';
import { useState, useRef, useCallback } from 'react';
import { UserPlus, Upload, Search, Tag, Trash2, Eye, Users, Table2, Download, CheckSquare, Square, X, MessageSquare, Pencil, Save } from 'lucide-react';
import { useTranslation } from 'react-i18next';

function ContactAvatar({ contact, size = 8 }) {
    const { t } = useTranslation();
    const name = `${contact.first_name ?? ''} ${contact.last_name ?? ''}`.trim();
    const initials = name
        ? name.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase()
        : '?';

    if (contact.avatar_url) {
        return (
            <img
                src={contact.avatar_url}
                alt={name || t('contacts_page.contact_alt')}
                className={`h-${size} w-${size} rounded-full object-cover flex-shrink-0`}
                onError={e => { e.target.style.display = 'none'; e.target.nextSibling.style.display = 'flex'; }}
            />
        );
    }
    return (
        <div className={`h-${size} w-${size} rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center text-xs font-semibold flex-shrink-0`}>
            {initials}
        </div>
    );
}

function ContactRow({ contact, selected, onToggle, onDelete, onEdit }) {
    const { t } = useTranslation();
    return (
        <tr className={`hover:bg-neutral-50 dark:hover:bg-neutral-800/50 ${selected ? 'bg-brand-50 dark:bg-brand-900/10' : ''}`}>
            <td className="px-4 py-3">
                <button type="button" onClick={() => onToggle(contact.uuid)} className="text-neutral-400 hover:text-brand-600 transition">
                    {selected ? <CheckSquare className="h-4 w-4 text-brand-600" /> : <Square className="h-4 w-4" />}
                </button>
            </td>
            <td className="px-4 py-3">
                <div className="flex items-center gap-3">
                    <ContactAvatar contact={contact} />
                    <span className="font-medium text-neutral-900 dark:text-neutral-100">
                        {(`${contact.first_name ?? ''} ${contact.last_name ?? ''}`).trim() || (
                            <span className="text-neutral-400">{t('common.unnamed')}</span>
                        )}
                    </span>
                </div>
            </td>
            <td className="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{contact.phone_e164 || '—'}</td>
            <td className="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">{contact.email || '—'}</td>
            <td className="px-4 py-3">
                <div className="flex flex-wrap gap-1">
                    {contact.tags?.map(tag => (
                        <span key={tag.id} className="rounded-full px-2 py-0.5 text-xs font-medium" style={{ backgroundColor: tag.color + '33', color: tag.color }}>
                            {tag.name}
                        </span>
                    ))}
                    {(!contact.tags || contact.tags.length === 0) && <span className="text-neutral-400 text-xs">—</span>}
                </div>
            </td>
            <td className="px-4 py-3">
                <div className="flex flex-wrap gap-1">
                    {contact.segments?.map(seg => (
                        <span key={seg.id} className="rounded bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 px-2 py-0.5 text-xs font-medium border border-neutral-200 dark:border-neutral-700">
                            {seg.name}
                        </span>
                    ))}
                    {(!contact.segments || contact.segments.length === 0) && <span className="text-neutral-400 text-xs">—</span>}
                </div>
            </td>
            <td className="px-4 py-3">
                <div className="flex items-center gap-1">
                    {contact.opt_in_whatsapp && <span className="text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 rounded px-1">{t('contacts_page.channel_wa')}</span>}
                    {contact.opt_in_email    && <span className="text-xs bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 rounded px-1">{t('contacts_page.channel_email')}</span>}
                </div>
            </td>
            <td className="px-4 py-3">
                <div className="flex items-center gap-2">
                    <a
                        href={`/app/inbox?contact_id=${contact.id}`}
                        title="Chat on WhatsApp"
                        className="p-1 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition"
                    >
                        <MessageSquare className="h-4 w-4" />
                    </a>
                    <button
                        type="button"
                        onClick={() => onEdit(contact)}
                        title="Edit Contact"
                        className="p-1 rounded-lg text-neutral-400 hover:text-brand-600 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                    >
                        <Pencil className="h-4 w-4" />
                    </button>
                    <Link href={route('client.contacts.show', contact.uuid)} className="p-1 rounded-lg text-neutral-400 hover:text-brand-600 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                        <Eye className="h-4 w-4" />
                    </Link>
                    <button type="button" onClick={() => onDelete(contact.uuid)} className="p-1 rounded-lg text-neutral-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition">
                        <Trash2 className="h-4 w-4" />
                    </button>
                </div>
            </td>
        </tr>
    );
}

export default function ContactsIndex({ contacts, filters, segments = [], tags = [] }) {
    const { t } = useTranslation();
    const { props } = usePage();
    const flash = props.flash ?? {};
    const [search, setSearch] = useState(filters.search ?? '');
    const [selectedSegment, setSelectedSegment] = useState(filters.segment ?? '');
    const [selectedTag, setSelectedTag] = useState(filters.tag ?? '');
    const [showAddModal, setShowAddModal] = useState(false);
    const [editContact, setEditContact] = useState(null);
    const [selected, setSelected] = useState(new Set());
    const fileInput = useRef();

    const [showTagsModal, setShowTagsModal] = useState(false);
    const [newTagName, setNewTagName] = useState('');
    const [newTagColor, setNewTagColor] = useState('#6366F1');
    const [creatingTag, setCreatingTag] = useState(false);

    // ── Add contact form ────────────────────────────────────────────────
    const { data, setData, post, processing, reset } = useForm({
        first_name: '', last_name: '', phone_e164: '', email: '',
        opt_in_whatsapp: true, opt_in_sms: true, opt_in_email: true,
        segment_ids: [],
        tag_ids: [],
    });

    // ── Edit contact form ────────────────────────────────────────────────
    const { data: editData, setData: setEditData, put, processing: editProcessing, reset: resetEdit, errors: editErrors } = useForm({
        first_name: '',
        last_name: '',
        phone_e164: '',
        email: '',
        opt_in_whatsapp: true,
        opt_in_sms: true,
        opt_in_email: true,
        segment_ids: [],
        tag_ids: [],
    });

    const openEditModal = (contact) => {
        setEditContact(contact);
        setEditData({
            first_name: contact.first_name ?? '',
            last_name: contact.last_name ?? '',
            phone_e164: contact.phone_e164 ?? '',
            email: contact.email ?? '',
            opt_in_whatsapp: contact.opt_in_whatsapp ?? true,
            opt_in_sms: contact.opt_in_sms ?? true,
            opt_in_email: contact.opt_in_email ?? true,
            segment_ids: (contact.segments ?? []).map(s => s.id),
            tag_ids: (contact.tags ?? []).map(t => t.id),
        });
    };

    const handleCreateTag = (e) => {
        e.preventDefault();
        setCreatingTag(true);
        router.post(route('client.tags.store'), {
            name: newTagName,
            color: newTagColor,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setNewTagName('');
                setNewTagColor('#6366F1');
                setCreatingTag(false);
            },
            onError: () => setCreatingTag(false),
        });
    };

    const handleDeleteTag = (id) => {
        if (!confirm('Are you sure you want to delete this tag? This will remove the tag from all contacts.')) return;
        router.delete(route('client.tags.destroy', id), {
            preserveScroll: true,
        });
    };

    const closeEditModal = () => {
        setEditContact(null);
        resetEdit();
    };

    const submitEdit = (e) => {
        e.preventDefault();
        put(route('client.contacts.update', editContact.uuid), {
            preserveScroll: true,
            onSuccess: () => closeEditModal(),
        });
    };

    const allUuids = contacts.data.map(c => c.uuid);
    const allSelected = allUuids.length > 0 && allUuids.every(id => selected.has(id));
    const someSelected = selected.size > 0;

    const toggleAll = useCallback(() => {
        if (allSelected) {
            setSelected(prev => { const next = new Set(prev); allUuids.forEach(id => next.delete(id)); return next; });
        } else {
            setSelected(prev => new Set([...prev, ...allUuids]));
        }
    }, [allSelected, allUuids]);

    const toggleOne = useCallback((uuid) => {
        setSelected(prev => { const next = new Set(prev); next.has(uuid) ? next.delete(uuid) : next.add(uuid); return next; });
    }, []);

    const clearSelection = () => setSelected(new Set());

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('client.contacts.index'), { search, segment: selectedSegment, tag: selectedTag }, { preserveState: true, replace: true });
    };

    const handleDelete = (uuid) => {
        if (confirm(t('contacts_page.confirm_delete_one'))) {
            router.delete(route('client.contacts.destroy', uuid), { preserveScroll: true });
        }
    };

    const handleBulkDelete = () => {
        if (!confirm(t('contacts_page.confirm_delete_selected', { count: selected.size }))) return;
        router.delete(route('client.contacts.bulk-destroy'), {
            data: { uuids: [...selected] },
            preserveScroll: true,
            onSuccess: () => clearSelection(),
        });
    };

    const handleExport = (selectedOnly = false) => {
        const params = new URLSearchParams();
        if (selectedOnly && someSelected) {
            params.set('uuids', [...selected].join(','));
        } else if (filters.search) {
            params.set('search', filters.search);
        }
        window.location.href = route('client.contacts.export') + (params.toString() ? '?' + params.toString() : '');
    };

    const handleImport = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('file', file);
        router.post(route('client.contacts.import'), formData, { preserveScroll: true });
    };

    const handlePhoneChange = (value) => {
        setData(prev => ({
            ...prev,
            phone_e164: value,
            opt_in_whatsapp: value.trim() ? prev.opt_in_whatsapp : false,
            opt_in_sms: value.trim() ? prev.opt_in_sms : false,
        }));
    };

    const handleEmailChange = (value) => {
        setData(prev => ({
            ...prev,
            email: value,
            opt_in_email: value.trim() ? prev.opt_in_email : false,
        }));
    };

    const submitAdd = (e) => {
        e.preventDefault();
        if (!data.phone_e164.trim() && !data.email.trim()) {
            alert(t('contacts_page.alert_phone_or_email'));
            return;
        }
        post(route('client.contacts.store'), { onSuccess: () => { reset(); setShowAddModal(false); } });
    };

    return (
        <ClientLayout title={t('contacts_page.title')}>
            <Head title={t('contacts_page.title')} />
            <div className="space-y-5">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h2 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{t('contacts_page.title')}</h2>
                    <div className="flex gap-2">
                        {(
                            <Link
                                href={route('client.contacts.bulk-import')}
                                className="flex items-center gap-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                            >
                                <Table2 className="h-4 w-4" /> {t('contacts_page.bulk_import')}
                            </Link>
                        )}
                        {(
                            <button type="button" onClick={() => fileInput.current?.click()} className="flex items-center gap-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                                <Upload className="h-4 w-4" /> {t('contacts_page.import_csv')}
                            </button>
                        )}
                        <input ref={fileInput} type="file" accept=".csv,.txt" className="hidden" onChange={handleImport} />
                        <button type="button" onClick={() => handleExport(false)} className="flex items-center gap-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                            <Download className="h-4 w-4" /> {t('contacts_page.export_csv')}
                        </button>
                        <Link href={route('client.segments.index')} className="flex items-center gap-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                            <Tag className="h-4 w-4" /> {t('contacts_page.segments')}
                        </Link>
                        <button
                            type="button"
                            onClick={() => setShowTagsModal(true)}
                            className="flex items-center gap-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                        >
                            <Tag className="h-4 w-4" /> Manage Tags
                        </button>
                        {(
                            <button type="button" onClick={() => setShowAddModal(true)} className="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700 transition">
                                <UserPlus className="h-4 w-4" /> {t('contacts_page.add_contact')}
                            </button>
                        )}
                    </div>
                </div>

                {flash.success && <div className="rounded-lg bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-4 py-2 text-sm">{flash.success}</div>}

                {/* Search & Filters */}
                <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-2">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" />
                        <input
                            type="text"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder={t('contacts_page.search_placeholder')}
                            className="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 pl-9 pr-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                        />
                    </div>
                    <div className="flex gap-2">
                        {segments.length > 0 && (
                            <select
                                value={selectedSegment}
                                onChange={e => {
                                    setSelectedSegment(e.target.value);
                                    router.get(route('client.contacts.index'), {
                                        search,
                                        segment: e.target.value,
                                        tag: selectedTag
                                    }, { preserveState: true, replace: true });
                                }}
                                className="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            >
                                <option value="">All Segments</option>
                                {segments.map(seg => (
                                    <option key={seg.id} value={seg.id}>{seg.name}</option>
                                ))}
                            </select>
                        )}
                        {tags.length > 0 && (
                            <select
                                value={selectedTag}
                                onChange={e => {
                                    setSelectedTag(e.target.value);
                                    router.get(route('client.contacts.index'), {
                                        search,
                                        segment: selectedSegment,
                                        tag: e.target.value
                                    }, { preserveState: true, replace: true });
                                }}
                                className="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            >
                                <option value="">All Tags</option>
                                {tags.map(tag => (
                                    <option key={tag.id} value={tag.name}>{tag.name}</option>
                                ))}
                            </select>
                        )}
                        <button type="submit" className="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition">{t('common.search')}</button>
                    </div>
                </form>

                {/* Bulk action bar */}
                {someSelected && (
                    <div className="flex items-center gap-3 rounded-lg bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-700 px-4 py-2.5">
                        <span className="text-sm font-medium text-brand-700 dark:text-brand-300">{t('contacts_page.n_selected', { count: selected.size })}</span>
                        <div className="flex gap-2 ml-auto">
                            <button type="button" onClick={() => handleExport(true)} className="flex items-center gap-1.5 rounded-lg border border-brand-300 dark:border-brand-600 px-3 py-1.5 text-xs font-medium text-brand-700 dark:text-brand-300 hover:bg-brand-100 dark:hover:bg-brand-900/40 transition">
                                <Download className="h-3.5 w-3.5" /> {t('contacts_page.export_selected')}
                            </button>
                            <button type="button" onClick={handleBulkDelete} className="flex items-center gap-1.5 rounded-lg border border-red-300 dark:border-red-700 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                <Trash2 className="h-3.5 w-3.5" /> {t('contacts_page.delete_selected')}
                            </button>
                            <button type="button" onClick={clearSelection} className="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition">
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                )}

                {/* Table */}
                <div className="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900">
                    <table className="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                        <thead className="bg-neutral-50 dark:bg-neutral-800">
                            <tr>
                                <th className="px-4 py-3 w-10">
                                    <button type="button" onClick={toggleAll} className="text-neutral-400 hover:text-brand-600 transition">
                                        {allSelected
                                            ? <CheckSquare className="h-4 w-4 text-brand-600" />
                                            : <Square className="h-4 w-4" />
                                        }
                                    </button>
                                </th>
                                {[t('common.name'), t('contacts_page.col_phone'), t('common.email'), t('contacts_page.col_tags'), 'Segments', t('contacts_page.col_optins'), ''].map(h => (
                                    <th key={h} className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-neutral-100 dark:divide-neutral-800">
                            {contacts.data.map(c => (
                                <ContactRow
                                    key={c.id}
                                    contact={c}
                                    selected={selected.has(c.uuid)}
                                    onToggle={toggleOne}
                                    onDelete={handleDelete}
                                    onEdit={openEditModal}
                                />
                            ))}
                            {contacts.data.length === 0 && (
                                <tr>
                                    <td colSpan={8}>
                                        <EmptyState
                                            icon={<Users className="h-8 w-8" />}
                                            title={t('contacts_page.empty_title')}
                                            description={t('contacts_page.empty_description')}
                                            action={{ label: t('contacts_page.add_contact'), onClick: () => setShowAddModal(true) }}
                                            secondaryAction={{ label: t('contacts_page.bulk_import'), href: route('client.contacts.bulk-import') }}
                                        />
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {contacts.last_page > 1 && (
                    <div className="flex gap-1">
                        {contacts.links.map((link, i) => (
                            <a key={i} href={link.url ?? '#'} className={`px-3 py-1.5 rounded text-sm border ${link.active ? 'bg-brand-600 text-white border-brand-600' : 'border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800'} ${!link.url ? 'opacity-40 pointer-events-none' : ''}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                        ))}
                    </div>
                )}
            </div>

            {/* ── Add Contact Modal ─────────────────────────────────────────── */}
            {showAddModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white dark:bg-neutral-900 p-6 shadow-xl space-y-4">
                        <h3 className="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{t('contacts_page.add_contact')}</h3>
                        <form onSubmit={submitAdd} className="space-y-3">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">{t('contacts_page.first_name')}</label>
                                    <input type="text" value={data.first_name} onChange={e => setData('first_name', e.target.value)} className="mt-1 w-full rounded border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-1.5 text-sm" />
                                </div>
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">{t('contacts_page.last_name')}</label>
                                    <input type="text" value={data.last_name} onChange={e => setData('last_name', e.target.value)} className="mt-1 w-full rounded border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-1.5 text-sm" />
                                </div>
                            </div>
                            <div>
                                <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">{t('contacts_page.phone_e164')}</label>
                                <input type="text" value={data.phone_e164} onChange={e => handlePhoneChange(e.target.value)} placeholder="+8801XXXXXXXXX" className="mt-1 w-full rounded border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-1.5 text-sm" />
                            </div>
                            <div>
                                <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">{t('common.email')}</label>
                                <input type="email" value={data.email} onChange={e => handleEmailChange(e.target.value)} className="mt-1 w-full rounded border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-1.5 text-sm" />
                            </div>
                            <div className="flex gap-4">
                                {[['opt_in_whatsapp', 'WhatsApp', !data.phone_e164.trim()], ['opt_in_email', t('common.email'), !data.email.trim()]].map(([key, label, disabled]) => (
                                    <label key={key} className={`flex items-center gap-1.5 text-sm ${disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'}`}>
                                        <input type="checkbox" checked={data[key]} onChange={e => setData(key, e.target.checked)} disabled={disabled} className="rounded" />
                                        {label}
                                    </label>
                                ))}
                            </div>
                            {segments.length > 0 && (
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">{t('contacts_page.add_to_segments')}</label>
                                    <div className="mt-1.5 flex flex-wrap gap-2">
                                        {segments.map(seg => {
                                            const checked = data.segment_ids.includes(seg.id);
                                            return (
                                                <label key={seg.id} className={`flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs cursor-pointer transition ${checked ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-400 hover:border-brand-400'}`}>
                                                    <input type="checkbox" className="sr-only" checked={checked} onChange={() => {
                                                        const ids = checked ? data.segment_ids.filter(id => id !== seg.id) : [...data.segment_ids, seg.id];
                                                        setData('segment_ids', ids);
                                                    }} />
                                                    {seg.name}
                                                </label>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                            {tags.length > 0 && (
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Batches (Tags)</label>
                                    <div className="mt-1.5 flex flex-wrap gap-2">
                                        {tags.map(tag => {
                                            const checked = data.tag_ids.includes(tag.id);
                                            return (
                                                <label key={tag.id} className={`flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs cursor-pointer transition ${checked ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-400 hover:border-brand-400'}`}>
                                                    <input type="checkbox" className="sr-only" checked={checked} onChange={() => {
                                                        const ids = checked ? data.tag_ids.filter(id => id !== tag.id) : [...data.tag_ids, tag.id];
                                                        setData('tag_ids', ids);
                                                    }} />
                                                    <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: tag.color }} />
                                                    {tag.name}
                                                </label>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                            <div className="flex gap-2 pt-2">
                                <button type="submit" disabled={processing} className="flex-1 rounded-lg bg-brand-600 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-60 transition">
                                    {processing ? t('common.saving') : t('common.save')}
                                </button>
                                <button type="button" onClick={() => setShowAddModal(false)} className="rounded-lg border border-neutral-300 dark:border-neutral-600 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                                    {t('common.cancel')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* ── Inline Edit Contact Modal ─────────────────────────────────── */}
            {editContact && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white dark:bg-neutral-900 shadow-2xl overflow-hidden">
                        {/* Modal Header */}
                        <div className="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800">
                            <div className="flex items-center gap-3">
                                <ContactAvatar contact={editContact} size={9} />
                                <div>
                                    <h3 className="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Edit Contact</h3>
                                    <p className="text-xs text-neutral-500 dark:text-neutral-400">{editContact.phone_e164 || editContact.email || 'No contact info'}</p>
                                </div>
                            </div>
                            <button type="button" onClick={closeEditModal} className="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-600 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition">
                                <X className="h-4 w-4" />
                            </button>
                        </div>

                        {/* Modal Body */}
                        <form onSubmit={submitEdit} className="p-6 space-y-4">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">First Name</label>
                                    <input
                                        type="text"
                                        value={editData.first_name}
                                        onChange={e => setEditData('first_name', e.target.value)}
                                        className="mt-1 w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    />
                                    {editErrors.first_name && <p className="mt-1 text-xs text-red-500">{editErrors.first_name}</p>}
                                </div>
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Last Name</label>
                                    <input
                                        type="text"
                                        value={editData.last_name}
                                        onChange={e => setEditData('last_name', e.target.value)}
                                        className="mt-1 w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Phone (E.164 format)</label>
                                <input
                                    type="text"
                                    value={editData.phone_e164}
                                    onChange={e => setEditData('phone_e164', e.target.value)}
                                    placeholder="+919876543210"
                                    className="mt-1 w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                />
                                {editErrors.phone_e164 && <p className="mt-1 text-xs text-red-500">{editErrors.phone_e164}</p>}
                            </div>

                            <div>
                                <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Email</label>
                                <input
                                    type="email"
                                    value={editData.email}
                                    onChange={e => setEditData('email', e.target.value)}
                                    className="mt-1 w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                />
                                {editErrors.email && <p className="mt-1 text-xs text-red-500">{editErrors.email}</p>}
                            </div>

                            {/* Opt-ins */}
                            <div>
                                <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Communication Opt-ins</label>
                                <div className="mt-2 flex flex-wrap gap-3">
                                    {[
                                        ['opt_in_whatsapp', '💬 WhatsApp'],
                                        ['opt_in_sms', '📱 SMS'],
                                        ['opt_in_email', '📧 Email'],
                                    ].map(([key, label]) => (
                                        <label key={key} className="flex items-center gap-2 cursor-pointer text-sm text-neutral-700 dark:text-neutral-300">
                                            <input
                                                type="checkbox"
                                                checked={editData[key]}
                                                onChange={e => setEditData(key, e.target.checked)}
                                                className="rounded border-neutral-300 text-brand-600 focus:ring-brand-500"
                                            />
                                            {label}
                                        </label>
                                    ))}
                                </div>
                            </div>

                            {/* Segments Selection */}
                            {segments.length > 0 && (
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Segments</label>
                                    <div className="mt-1.5 flex flex-wrap gap-2">
                                        {segments.map(seg => {
                                            const checked = editData.segment_ids?.includes(seg.id);
                                            return (
                                                <label key={seg.id} className={`flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs cursor-pointer transition ${checked ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-400 hover:border-brand-400'}`}>
                                                    <input type="checkbox" className="sr-only" checked={checked} onChange={() => {
                                                        const ids = checked ? editData.segment_ids.filter(id => id !== seg.id) : [...(editData.segment_ids ?? []), seg.id];
                                                        setEditData('segment_ids', ids);
                                                    }} />
                                                    {seg.name}
                                                </label>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                            {/* Tags Selection */}
                            {tags.length > 0 && (
                                <div>
                                    <label className="text-xs font-medium text-neutral-500 dark:text-neutral-400">Batches (Tags)</label>
                                    <div className="mt-1.5 flex flex-wrap gap-2">
                                        {tags.map(tag => {
                                            const checked = editData.tag_ids?.includes(tag.id);
                                            return (
                                                <label key={tag.id} className={`flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs cursor-pointer transition ${checked ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-400 hover:border-brand-400'}`}>
                                                    <input type="checkbox" className="sr-only" checked={checked} onChange={() => {
                                                        const ids = checked ? editData.tag_ids.filter(id => id !== tag.id) : [...(editData.tag_ids ?? []), tag.id];
                                                        setEditData('tag_ids', ids);
                                                    }} />
                                                    <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: tag.color }} />
                                                    {tag.name}
                                                </label>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}

                            {/* Actions */}
                            <div className="flex gap-2 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                                <button
                                    type="submit"
                                    disabled={editProcessing}
                                    className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 py-2.5 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-60 transition"
                                >
                                    <Save className="h-4 w-4" />
                                    {editProcessing ? 'Saving...' : 'Save Changes'}
                                </button>
                                <button
                                    type="button"
                                    onClick={closeEditModal}
                                    className="rounded-lg border border-neutral-300 dark:border-neutral-600 px-5 py-2.5 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            {/* ── Manage Tags Modal ────────────────────────────────────────── */}
            {showTagsModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-md rounded-xl bg-white dark:bg-neutral-900 p-6 shadow-xl space-y-4 max-h-[85vh] flex flex-col">
                        <div className="flex items-center justify-between border-b pb-2">
                            <h3 className="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Manage Tags</h3>
                            <button type="button" onClick={() => setShowTagsModal(false)} className="text-neutral-400 hover:text-neutral-600">
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        {/* Tag list */}
                        <div className="flex-1 overflow-y-auto space-y-2 max-h-[40vh] pr-1">
                            {tags.length === 0 && (
                                <p className="text-sm text-neutral-500 text-center py-4">No tags created yet.</p>
                            )}
                            {tags.map(tag => (
                                <div key={tag.id} className="flex items-center justify-between p-2 rounded-lg bg-neutral-50 dark:bg-neutral-800 border">
                                    <span className="flex items-center gap-2 text-sm font-medium text-neutral-800 dark:text-neutral-200">
                                        <span className="w-3.5 h-3.5 rounded-full shrink-0" style={{ backgroundColor: tag.color }} />
                                        {tag.name}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => handleDeleteTag(tag.id)}
                                        className="p-1 text-neutral-400 hover:text-red-500 rounded transition"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>
                            ))}
                        </div>

                        {/* Add tag form */}
                        <form onSubmit={handleCreateTag} className="border-t pt-3 space-y-3">
                            <h4 className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Create New Tag</h4>
                            <div className="flex gap-2">
                                <input
                                    type="text"
                                    placeholder="Tag Name (e.g. VIP, Morning Batch)"
                                    value={newTagName}
                                    onChange={e => setNewTagName(e.target.value)}
                                    required
                                    className="flex-1 rounded border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-1.5 text-sm"
                                />
                                <input
                                    type="color"
                                    value={newTagColor}
                                    onChange={e => setNewTagColor(e.target.value)}
                                    className="w-10 h-9 p-0.5 rounded border border-neutral-300 dark:border-neutral-600 cursor-pointer"
                                />
                            </div>
                            <button
                                type="submit"
                                disabled={creatingTag}
                                className="w-full rounded-lg bg-brand-600 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-60 transition"
                            >
                                {creatingTag ? 'Creating...' : 'Create Tag'}
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </ClientLayout>
    );
}
