import { useState } from 'react';

import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Label } from '@components/ui/label';

import { useApi } from '@/lib/api';
import { Team } from '@/types/models';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@components/ui/dialog';

interface DeactivateTeamProps {
    team: Team;
    updateTeam: (newTeam: Team) => void;
}

export default function DeactivateTeam({ team, updateTeam }: DeactivateTeamProps) {
    const api = useApi();
    const [teamName, setTeamName] = useState<string>('');
    const [processing, setProcessing] = useState<boolean>(false);
    const [errors, setErrors] = useState<{ general?: string }>({});

    const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
        if (processing) return;

        event.preventDefault();

        if (teamName !== team.name) {
            setErrors({ general: 'Team name does not match.' });
            return;
        }

        setProcessing(true);
        setErrors({});

        const response = await api.deactivateTeam({ id: team?.id as number });

        setProcessing(false);

        if (response.status >= 400) {
            setErrors(response.errors || { general: 'An unexpected error occurred.' });
            return;
        }

        closeModal();
        updateTeam({
            ...team,
            ...response.data,
        });
    };

    const closeModal = () => {
        setTeamName('');
        setErrors({});
        setProcessing(false);
    };

    return (
        <div className="space-y-6">
            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">Deactivate Team</p>
                    <p className="text-sm">
                        The team and its memberships will remain, but it will no longer grant roles or permissions until reactivated.
                    </p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="destructive">Deactivate</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>Are you sure you want to deactivate this team?</DialogTitle>
                        <DialogDescription>Type "{team.name}" to confirm deactivation of this team.</DialogDescription>
                        <form className="space-y-6" onSubmit={handleSubmit}>
                            <div className="grid gap-2">
                                <Label htmlFor="name" className="sr-only">
                                    Team Name
                                </Label>

                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value={teamName}
                                    onChange={(e) => {
                                        setTeamName(e.target.value);
                                        setErrors({});
                                    }}
                                />

                                <InputError message={errors.general} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary" onClick={closeModal}>
                                        Cancel
                                    </Button>
                                </DialogClose>

                                <Button variant="destructive" disabled={processing} asChild>
                                    <button type="submit">Deactivate</button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
