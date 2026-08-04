import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { ChannelBrandIcon } from '@/Components/BrandIcons';
import {
    LayoutDashboard, CreditCard, Package, FileText, Users, Settings,
    Layers, Webhook, Key, BookOpen, Image, Radio, Inbox, Bot, Database,
    Zap, Share2, MapPin, Tag, LifeBuoy, ExternalLink, Mail, MessageSquare, Calendar
} from 'lucide-react';

const iconClass = 'h-4 w-4';
const whatsappNavIcon = <ChannelBrandIcon channel="whatsapp" className={iconClass} />;

function safeRoute(name, ...args) {
    try { return route(name, ...args); } catch { return '#'; }
}

/**
 * Single source of truth for the client-panel sidebar navigation.
 *
 * Both ClientLayout and InboxLayout consume this so the sidebar is identical on
 * every client page. Previously each layout kept its own copy and they drifted —
 * the inbox sidebar was missing whole groups (Social Media, Automations, Leads)
 * and items. Keep all nav changes here only.
 */
export default function useClientNav() {
    const { auth, branding, features = {} } = usePage().props;
    const { t } = useTranslation();
    const user = auth?.user;
    const docsUrl = branding?.docs_url;
    const isClientAdmin = user?.client_role === 'administrator';

    const accountItems = [
        { label: t('nav.dashboard'), href: safeRoute('client.dashboard'), icon: <LayoutDashboard className={iconClass} />, activePattern: 'client.dashboard' },
        { label: 'Classes & Calendar', href: safeRoute('client.meetings.index'), icon: <Calendar className={iconClass} />, activePattern: 'client.meetings.*' },
        { label: 'Integrations & Google', href: '/app/integrations', icon: <Share2 className={iconClass} />, activePattern: 'client.integrations.*' },
    ];

    const accountSettingsItems = [
        { label: t('nav.workspaces'), href: safeRoute('client.workspaces.index'), icon: <Layers className={iconClass} />,   activePattern: 'client.workspaces.*' },
        { label: 'Integrations & Google', href: '/app/integrations', icon: <Share2 className={iconClass} />, activePattern: 'client.integrations.*' },
        { label: t('nav.settings'),   href: safeRoute('client.settings.index'),   icon: <Settings className={iconClass} />, activePattern: 'client.settings.*' },
    ];

    if (isClientAdmin) {
        accountSettingsItems.push(
            { label: t('nav.team'),      href: safeRoute('client.team.index'),      icon: <Users className={iconClass} />,    activePattern: 'client.team.*' },
            { label: t('nav.audit_log'), href: safeRoute('client.audit-log.index'), icon: <FileText className={iconClass} />, activePattern: 'client.audit-log.*' },
        );
    }

    const billingItems = [];

    const developerItems = [
        { label: t('nav.api_tokens'),    href: safeRoute('client.api-tokens.index'), icon: <Key className={iconClass} />,     activePattern: 'client.api-tokens.*' },
        { label: t('nav.webhooks'),      href: safeRoute('client.webhooks.index'),    icon: <Webhook className={iconClass} />,  activePattern: 'client.webhooks.*' },
        { label: t('nav.api_docs'),      href: safeRoute('client.api-docs'),          icon: <BookOpen className={iconClass} />, activePattern: 'client.api-docs' },
        { label: t('nav.media_library'), href: safeRoute('client.media.index'),       icon: <Image className={iconClass} />,   activePattern: 'client.media.*' },
    ];

    const supportItems = [
        { label: t('nav.support_tickets'), href: safeRoute('client.support.index'), icon: <LifeBuoy className={iconClass} />,   activePattern: 'client.support.*' },
    ];

    if (docsUrl) {
        supportItems.push({ label: t('nav.help_docs'), href: docsUrl, icon: <ExternalLink className={iconClass} />, external: true });
    }

    const contactsItems = [
        { label: t('nav.contacts'),  href: safeRoute('client.contacts.index'),  icon: <Users className={iconClass} />,  activePattern: 'client.contacts.*' },
        { label: t('nav.segments'),  href: safeRoute('client.segments.index'),  icon: <Tag className={iconClass} />,    activePattern: 'client.segments.*' },
    ];

    const messagingItems = [
        { label: t('nav.templates'),     href: safeRoute('client.whatsapp.templates.index'),     icon: whatsappNavIcon, activePattern: 'client.whatsapp.templates.*' },
        { label: t('nav.auto_replies'),  href: safeRoute('client.whatsapp.auto-replies.index'),  icon: whatsappNavIcon, activePattern: 'client.whatsapp.auto-replies.*' },
        { label: t('nav.chat_widget'),   href: safeRoute('client.whatsapp.widget.index'),         icon: whatsappNavIcon, activePattern: 'client.whatsapp.widget.*' },
    ];

    const broadcastItems = [
        { label: t('nav.campaigns'),    href: safeRoute('client.campaigns.index'),    icon: <Radio className={iconClass} />,        activePattern: 'client.campaigns.*' },
        { label: t('nav.sms_gateways'), href: safeRoute('client.sms-gateways.index'), icon: <MessageSquare className={iconClass} />, activePattern: 'client.sms-gateways.*' },
        { label: t('nav.email_server'), href: safeRoute('client.email-server.index'), icon: <Mail className={iconClass} />,          activePattern: 'client.email-server.*' },
    ];

    const inboxItems = [
        { label: t('nav.inbox'),         href: safeRoute('client.inbox.index'), icon: <Inbox className={iconClass} />, activePattern: 'client.inbox.index' },
        { label: t('nav.channel_setup'), href: safeRoute('client.inbox.setup'), icon: <Inbox className={iconClass} />, activePattern: 'client.inbox.setup' },
        { label: 'Google & Integrations', href: '/app/integrations', icon: <Share2 className={iconClass} />, activePattern: 'client.integrations.*' },
    ];

    const aiItems = [
        { label: t('nav.chatbots'),        href: safeRoute('client.ai.chatbots.index'),        icon: <Bot className={iconClass} />,      activePattern: 'client.ai.chatbots.*' },
        { label: t('nav.knowledge_bases'), href: safeRoute('client.ai.knowledge-bases.index'), icon: <Database className={iconClass} />, activePattern: 'client.ai.knowledge-bases.*' },
        { label: t('nav.ai_providers'),    href: safeRoute('client.ai.providers.index'),        icon: <Bot className={iconClass} />,      activePattern: 'client.ai.providers.*' },
    ];

    const socialItems = [
        { label: t('nav.post_composer'),   href: safeRoute('client.social.composer'),        icon: <FileText className={iconClass} />,       activePattern: 'client.social.composer' },
        { label: t('nav.posts'),           href: safeRoute('client.social.posts.index'),     icon: <Radio className={iconClass} />,           activePattern: 'client.social.posts.*' },
        { label: t('nav.calendar'),        href: safeRoute('client.social.calendar'),         icon: <LayoutDashboard className={iconClass} />, activePattern: 'client.social.calendar' },
        { label: t('nav.social_accounts'), href: safeRoute('client.social.accounts.index'),  icon: <Share2 className={iconClass} />,          activePattern: 'client.social.accounts.*' },
    ];

    const leadsItems = [
        { label: t('nav.lead_scraper'), href: safeRoute('client.leads.index'), icon: <MapPin className={iconClass} />, activePattern: 'client.leads.*' },
    ];

    const automationItems = [
        { label: t('nav.automations'), href: safeRoute('client.automations.index'), icon: <Zap className={iconClass} />, activePattern: 'client.automations.*' },
    ];


    const reportsItems = [
        { label: t('nav.reports_inbox'),       href: safeRoute('client.reports.inbox.index'),       icon: <Inbox className={iconClass} />,  activePattern: 'client.reports.inbox.*' },
        { label: t('nav.campaigns'),           href: safeRoute('client.reports.campaigns.index'),   icon: <Radio className={iconClass} />,  activePattern: 'client.reports.campaigns.*' },
        { label: t('nav.automations'),         href: safeRoute('client.reports.automations.index'), icon: <Zap className={iconClass} />,    activePattern: 'client.reports.automations.*' },
        { label: t('nav.ai_usage'),            href: safeRoute('client.reports.ai.index'),          icon: <Bot className={iconClass} />,    activePattern: 'client.reports.ai.*' },
        { label: t('nav.social'),              href: safeRoute('client.reports.social.index'),      icon: <Share2 className={iconClass} />, activePattern: 'client.reports.social.*' },
    ];

    // Group order: daily operations first, then growth tools, periodic review, then account-adjacent config (usage-frequency–based).
    const navGroups = [
        { type: 'group', label: 'Main',      items: accountItems },
        { type: 'group', label: t('nav.group_messaging'),     items: messagingItems },
        { type: 'group', label: t('nav.group_contacts'),      items: contactsItems },
    ];

    if (features.automations) {
        navGroups.push({ type: 'group', label: t('nav.automations') || 'Automations', items: automationItems });
    }

    navGroups.push({ type: 'group', label: t('nav.inbox') || 'Inbox & Channels', items: inboxItems });

    if (features.ai) {
        navGroups.push({ type: 'group', label: 'AI Tools', items: aiItems });
    }

    if (features.social_media) {
        navGroups.push({ type: 'group', label: 'Social Media', items: socialItems });
    }

    if (features.leads) {
        navGroups.push({ type: 'group', label: 'Leads', items: leadsItems });
    }
    
    // Add reports if there are any enabled features that generate reports
    if (features.ai || features.social_media || features.automations) {
        const activeReports = reportsItems.filter(item => {
            if (item.activePattern.includes('ai') && !features.ai) return false;
            if (item.activePattern.includes('social') && !features.social_media) return false;
            if (item.activePattern.includes('automations') && !features.automations) return false;
            return true;
        });
        navGroups.push({ type: 'group', label: 'Reports', items: activeReports });
    }

    navGroups.push({ type: 'group', label: 'Integrations & Developer', items: developerItems });
    navGroups.push({ type: 'group', label: 'Support', items: supportItems });
    navGroups.push({ type: 'group', label: 'Institute Admin', items: accountSettingsItems });

    return navGroups;
}
