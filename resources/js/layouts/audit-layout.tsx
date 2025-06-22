import { useGatekeeper } from '@/context/GatekeeperContext';
import { Alert, AlertDescription, AlertTitle } from '@components/ui/alert';
import Heading from '@components/ui/heading';
import { Ban } from 'lucide-react';
import { type PropsWithChildren } from 'react';

export default function AuditsLayout({ children }: PropsWithChildren) {
    if (typeof window === 'undefined') {
        return null;
    }

    const { config } = useGatekeeper();

    return (
        <div className="px-4 py-6">
            <Heading title="Audit Logs" description="Oversee Gatekeeper changes in your application" />

            {!config.audit_enabled && (
                <Alert className="mb-8 w-full max-w-xl lg:max-w-full">
                    <Ban className="s-4" />
                    <AlertTitle>Audit Feature Disabled</AlertTitle>
                    <AlertDescription>
                        Gatekeeper actions are not currently being logged. To capture all actions, please enable the audit feature in your Gatekeeper
                        configuration.
                    </AlertDescription>
                </Alert>
            )}

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <section className="space-y-12 lg:min-w-3xl">{children}</section>
            </div>
        </div>
    );
}
