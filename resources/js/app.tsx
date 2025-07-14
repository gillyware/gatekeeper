import '../css/app.css';

import { GatekeeperProvider } from '@/context/GatekeeperContext';
import GatekeeperApp from '@/GatekeeperApp';
import { initializeTheme } from '@/hooks/use-appearance';
import { initializeAxios } from '@/lib/axios';
import { type GatekeeperSharedData } from '@/types';
import { createRoot } from 'react-dom/client';

const container = document.getElementById('gatekeeper-root');
if (container) {
    const props: GatekeeperSharedData = (window as any).Gatekeeper || {};
    const root = createRoot(container);

    initializeTheme();
    initializeAxios();

    root.render(
        <GatekeeperProvider props={props}>
            <GatekeeperApp />
        </GatekeeperProvider>,
    );
}
