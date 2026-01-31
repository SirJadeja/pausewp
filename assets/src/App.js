/**
 * WP Pause Admin App - Main Component
 *
 * @package PauseWP
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {
    Card,
    CardHeader,
    CardBody,
    ToggleControl,
    TextControl,
    TextareaControl,
    Button,
    Spinner,
    Notice,
} from '@wordpress/components';

/**
 * Main App Component
 */
const App = () => {
    // State
    const [settings, setSettings] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [notice, setNotice] = useState(null);

    // Fetch settings on mount
    useEffect(() => {
        fetchSettings();
    }, []);

    /**
     * Fetch settings from REST API
     */
    const fetchSettings = async () => {
        try {
            setIsLoading(true);
            const data = await apiFetch({ path: '/pausewp/v1/settings' });
            setSettings(data);
        } catch (error) {
            setNotice({
                status: 'error',
                message: error.message || __('Failed to load settings.', 'pausewp'),
            });
        } finally {
            setIsLoading(false);
        }
    };

    /**
     * Save settings to REST API
     */
    const saveSettings = async () => {
        try {
            setIsSaving(true);
            setNotice(null);

            const data = await apiFetch({
                path: '/pausewp/v1/settings',
                method: 'POST',
                data: settings,
            });

            setSettings(data);
            setNotice({
                status: 'success',
                message: __('Settings saved successfully!', 'pausewp'),
            });
        } catch (error) {
            setNotice({
                status: 'error',
                message: error.message || __('Failed to save settings.', 'pausewp'),
            });
        } finally {
            setIsSaving(false);
        }
    };

    /**
     * Update a setting field
     */
    const updateSetting = (key, value) => {
        setSettings((prev) => ({
            ...prev,
            [key]: value,
        }));
    };

    // Loading state
    if (isLoading) {
        return (
            <div className="pausewp-loading">
                <Spinner />
                <span>{__('Loading settings...', 'pausewp')}</span>
            </div>
        );
    }

    // Error state (no settings)
    if (!settings) {
        return (
            <Notice status="error" isDismissible={false}>
                {__('Failed to load settings. Please refresh the page.', 'pausewp')}
            </Notice>
        );
    }

    return (
        <div className="pausewp-admin">
            <div className="pausewp-admin__header">
                <h1>{__('WP Pause - Maintenance Mode', 'pausewp')}</h1>
                <p className="pausewp-admin__description">
                    {__('Configure your maintenance mode settings below.', 'pausewp')}
                </p>
            </div>

            {notice && (
                <Notice
                    status={notice.status}
                    isDismissible
                    onDismiss={() => setNotice(null)}
                >
                    {notice.message}
                </Notice>
            )}

            <Card className="pausewp-admin__card">
                <CardHeader>
                    <h2>{__('Maintenance Status', 'pausewp')}</h2>
                </CardHeader>
                <CardBody>
                    <ToggleControl
                        label={__('Enable Maintenance Mode', 'pausewp')}
                        help={
                            settings.is_enabled
                                ? __('Site is currently in maintenance mode.', 'pausewp')
                                : __('Site is accessible to all visitors.', 'pausewp')
                        }
                        checked={settings.is_enabled}
                        onChange={(value) => updateSetting('is_enabled', value)}
                    />
                </CardBody>
            </Card>

            <Card className="pausewp-admin__card">
                <CardHeader>
                    <h2>{__('Content Settings', 'pausewp')}</h2>
                </CardHeader>
                <CardBody>
                    <TextControl
                        label={__('Heading', 'pausewp')}
                        value={settings.heading || ''}
                        onChange={(value) => updateSetting('heading', value)}
                        help={__('Main heading displayed on the maintenance page.', 'pausewp')}
                    />

                    <TextareaControl
                        label={__('Description', 'pausewp')}
                        value={settings.subheading || ''}
                        onChange={(value) => updateSetting('subheading', value)}
                        help={__('Subheading text. Basic HTML allowed (bold, links).', 'pausewp')}
                        rows={4}
                    />
                </CardBody>
            </Card>

            <Card className="pausewp-admin__card">
                <CardHeader>
                    <h2>{__('SEO Settings', 'pausewp')}</h2>
                </CardHeader>
                <CardBody>
                    <TextControl
                        label={__('Page Title', 'pausewp')}
                        value={settings.seo_title || ''}
                        onChange={(value) => updateSetting('seo_title', value)}
                        help={__('Browser tab title for the maintenance page.', 'pausewp')}
                    />

                    <TextareaControl
                        label={__('Meta Description', 'pausewp')}
                        value={settings.meta_description || ''}
                        onChange={(value) => updateSetting('meta_description', value)}
                        rows={2}
                    />
                </CardBody>
            </Card>

            <div className="pausewp-admin__actions">
                <Button
                    variant="primary"
                    onClick={saveSettings}
                    isBusy={isSaving}
                    disabled={isSaving}
                >
                    {isSaving ? __('Saving...', 'pausewp') : __('Save Settings', 'pausewp')}
                </Button>
            </div>
        </div>
    );
};

export default App;
