import { useGatekeeper } from '@/context/GatekeeperContext';
import AuditLogIndex from '@/screens/audit-log/AuditLogIndex';
import CreateEntityScreen from '@/screens/entity/CreateEntityScreen';
import EntityIndexScreen from '@/screens/entity/EntityIndexScreen';
import ManageEntityScreen from '@/screens/entity/ManageEntityScreen';
import LandingScreen from '@/screens/LandingScreen';
import ManageModelScreen from '@/screens/model/ManageModelScreen';
import ModelIndexScreen from '@/screens/model/ModelIndexScreen';
import { type GatekeeperPermission, type GatekeeperRole, type GatekeeperTeam } from '@/types';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';

const GatekeeperApp = () => {
    const { config } = useGatekeeper();

    return (
        <BrowserRouter basename={config.path}>
            <Routes>
                <Route path="/" element={<LandingScreen />} />

                <Route path="/permissions" element={<EntityIndexScreen<GatekeeperPermission> entity="permission" />} />
                <Route path="/permissions/create" element={<CreateEntityScreen<GatekeeperPermission> entity="permission" />} />
                <Route path="/permissions/:id/manage" element={<ManageEntityScreen entity="permission" />} />

                <Route path="/roles" element={<EntityIndexScreen<GatekeeperRole> entity="role" />} />
                <Route path="/roles/create" element={<CreateEntityScreen<GatekeeperRole> entity="role" />} />
                <Route path="/roles/:id/manage" element={<ManageEntityScreen entity="role" />} />

                <Route path="/teams" element={<EntityIndexScreen<GatekeeperTeam> entity="team" />} />
                <Route path="/teams/create" element={<CreateEntityScreen<GatekeeperTeam> entity="team" />} />
                <Route path="/teams/:id/manage" element={<ManageEntityScreen entity="team" />} />

                <Route path="/models" element={<ModelIndexScreen />} />
                <Route path="/models/:modelLabel/:modelPk" element={<ManageModelScreen />} />

                <Route path="/audit" element={<AuditLogIndex />} />

                <Route path="*" element={<Navigate to="/" />} />
            </Routes>
        </BrowserRouter>
    );
};

export default GatekeeperApp;
