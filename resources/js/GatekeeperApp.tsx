import { useGatekeeper } from '@/context/GatekeeperContext';
import AuditLogsIndex from '@/screens/audit-logs/AuditLogsIndex';
import LandingScreen from '@/screens/LandingScreen';
import ManageModel from '@/screens/models/ManageModel';
import ModelsIndex from '@/screens/models/ModelsIndex';
import CreatePermission from '@/screens/permissions/CreatePermission';
import ManagePermission from '@/screens/permissions/ManagePermission';
import PermissionsIndex from '@/screens/permissions/PermissionsIndex';
import CreateRole from '@/screens/roles/CreateRole';
import ManageRole from '@/screens/roles/ManageRole';
import RolesIndex from '@/screens/roles/RolesIndex';
import CreateTeam from '@/screens/teams/CreateTeam';
import ManageTeam from '@/screens/teams/ManageTeam';
import TeamsIndex from '@/screens/teams/TeamsIndex';
import { FC } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';

const GatekeeperApp: FC = () => {
    const { config } = useGatekeeper();

    return (
        <BrowserRouter basename={config.path}>
            <Routes>
                <Route path="/" element={<LandingScreen />} />

                <Route path="/permissions" element={<PermissionsIndex />} />
                <Route path="/permissions/create" element={<CreatePermission />} />
                <Route path="/permissions/:permissionId/manage" element={<ManagePermission />} />

                <Route path="/roles" element={<RolesIndex />} />
                <Route path="/roles/create" element={<CreateRole />} />
                <Route path="/roles/:roleId/manage" element={<ManageRole />} />

                <Route path="/teams" element={<TeamsIndex />} />
                <Route path="/teams/create" element={<CreateTeam />} />
                <Route path="/teams/:teamId/manage" element={<ManageTeam />} />

                <Route path="/models" element={<ModelsIndex />} />
                <Route path="/models/:modelLabel/:modelPk" element={<ManageModel />} />

                <Route path="/audit" element={<AuditLogsIndex />} />

                <Route path="*" element={<Navigate to="/" />} />
            </Routes>
        </BrowserRouter>
    );
};

export default GatekeeperApp;
