import { useGatekeeper } from '@/context/GatekeeperContext';
import { auditLogText, type AuditLogLayoutText } from '@/lib/lang/en/audit-log';
import { Alert, AlertDescription, AlertTitle } from '@components/ui/alert';
import Heading from '@components/ui/heading';
import { Ban } from 'lucide-react';
import { useMemo, type PropsWithChildren } from 'react';

export default function AuditLogLayout({ children }: PropsWithChildren) {
    if (typeof window === 'undefined') {
        return null;
    }

    const { config } = useGatekeeper();
    const language: AuditLogLayoutText = useMemo(() => auditLogText.layout, []);

    return (
        <div className="px-4 py-6">
            <Heading title={language.title} description={language.description} />

            {!config.audit_enabled && (
                <Alert className="mb-8 w-full max-w-xl lg:max-w-full">
                    <Ban className="s-4" />
                    <AlertTitle>{language.featureDisabledTitle}</AlertTitle>
                    <AlertDescription>{language.featureDisabledDescription}</AlertDescription>
                </Alert>
            )}

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <section className="space-y-12 lg:min-w-3xl">{children}</section>
            </div>
        </div>
    );
}
