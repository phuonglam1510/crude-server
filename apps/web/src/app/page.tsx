// Basic Next.js page to demonstrate API connection.
"use client";
import React, { useState, useEffect } from 'react';
import { AxiosError } from 'axios';
import apiClient from '../config/api';

interface Item {
    id: string;
    name: string;
    description?: string;
    createdAt?: Date;
    updatedAt?: Date;
}

export default function Home() {
    const [items, setItems] = useState<Item[]>([]);
    const [message, setMessage] = useState<string>('Loading...');

    useEffect(() => {
        apiClient.get<Item[]>('/api/resources')
            .then(response => {
                setItems(response.data);
                setMessage('');
            })
            .catch((error: AxiosError<{ message?: string }>) => {
                console.error("Failed to fetch items:", error);
                const errorMessage = error.response?.data?.message || error.message || 'Failed to fetch from API';
                setMessage(`Failed to connect to API: ${errorMessage}. Is the backend running?`);
            });
    }, []);

    return (
        <main className="flex min-h-screen flex-col items-center justify-center p-24 bg-gray-100" >
            <div className="z-10 w-full max-w-5xl items-center justify-between font-mono text-sm lg:flex" >
                <p className="fixed left-0 top-0 flex w-full justify-center border-b border-gray-300 bg-gradient-to-b from-zinc-200 pb-6 pt-8 backdrop-blur-2xl dark:border-neutral-800 dark:bg-zinc-800/30 dark:from-inherit lg:static lg:w-auto  lg:rounded-xl lg:border lg:bg-gray-200 lg:p-4 lg:dark:bg-zinc-800/30" >
                    End - User Web Application(Next.js)
                </p>
            </div>

            < div className="mt-20 p-8 bg-white rounded-lg shadow-xl" >
                <h1 className="text-2xl font-bold mb-4" > Items from Server </h1>
                {message && <p className="text-red-500" > {message} </p>}
                <ul className="list-disc pl-5" >
                    {
                        items.map(item => (
                            <li key={item.id} className="text-lg" > {item.name} </li>
                        ))
                    }
                </ul>
            </div>
        </main>
    );
}
