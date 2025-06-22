import { ConfiguredModel } from '@/types/api/model';
import { Card, CardContent } from '@components/ui/card';
import { Info } from 'lucide-react';

interface ModelSummaryProps {
    model: ConfiguredModel;
}

export default function ModelSummary({ model }: ModelSummaryProps) {
    return (
        <Card className="flex flex-row">
            <CardContent className="flex-1 gap-2">
                <div className="flex flex-row items-center justify-start gap-2">
                    <span className="min-w-[60px] font-bold">Model:</span>
                    <span>{model.model_label}</span>
                </div>

                {Object.entries(model.displayable).map(([displayableField, displayableFieldLabel]) => (
                    <div className="flex flex-row items-center justify-start gap-2">
                        <span className="min-w-[60px] font-bold">{displayableFieldLabel}:</span>
                        <span>{model.display[displayableField] ?? 'N/A'}</span>
                    </div>
                ))}
            </CardContent>
            <div className="flex items-start justify-center pr-6">
                <Info />
            </div>
        </Card>
    );
}
