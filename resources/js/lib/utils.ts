import { type QueryOrder } from '@/types/api/index';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}

export function swapOrder(order: QueryOrder | undefined): QueryOrder {
    return order === 'asc' ? 'desc' : 'asc';
}

export function flattenStrings(input: string | string[], separator: string = ' '): string {
    return Array.isArray(input) ? input.join(separator) : input;
}
