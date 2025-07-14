import { type GatekeeperSharedData } from '@/types';
import { createContext, type FC, type ReactNode, useContext } from 'react';

const GatekeeperContext = createContext<GatekeeperSharedData | undefined>(undefined);

interface GatekeeperProviderProps {
    children: ReactNode;
    props: GatekeeperSharedData;
}

export const GatekeeperProvider: FC<GatekeeperProviderProps> = ({ children, props }) => (
    <GatekeeperContext.Provider value={props}>{children}</GatekeeperContext.Provider>
);

export const useGatekeeper = (): GatekeeperSharedData => {
    const context = useContext(GatekeeperContext);
    if (!context) {
        throw new Error('useGatekeeper must be used within a GatekeeperProvider');
    }
    return context;
};
