import { type ModelManagementTab } from '@/types';
import { type ConfiguredModel } from '@/types/api/model';
import { createContext, useContext, type FC, type ReactNode } from 'react';

interface ModelContextData {
    model: ConfiguredModel;
    refreshModel: () => Promise<void>;
    tab: ModelManagementTab;
    setTab: (tab: ModelManagementTab) => void;
}

const ModelContext = createContext<ModelContextData | undefined>(undefined);

export const ModelProvider: FC<{ value: ModelContextData; children: ReactNode }> = ({ value, children }) => (
    <ModelContext.Provider value={value}>{children}</ModelContext.Provider>
);

export const useModel = (): ModelContextData => {
    const context = useContext(ModelContext);
    if (!context) throw new Error('useModel must be used within a ModelProvider');
    return context;
};
