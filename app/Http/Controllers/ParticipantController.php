<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantRequest;
use App\Http\Requests\UpdateParticipantRequest;
use App\Imports\ParticipantsImport;
use App\Exports\ParticipantsExport;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Services\ActivityLogService;
use App\Services\EventService;
use App\Services\ParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParticipantController extends Controller
{
    public function __construct(
        protected ParticipantService $participantService,
        protected EventService $eventService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function index(Request $request): View
    {
        $eventId = $this->eventService->requireActiveEventId();

        return view('participants.index', [
            'participants' => $this->participantService->paginate($request->only([
                'search', 'category_id', 'gender', 'status', 'sort_by', 'sort_dir',
            ])),
            'categories' => ParticipantCategory::where('event_id', $eventId)->orderBy('name')->get(),
            'filters' => $request->only(['search', 'category_id', 'gender', 'status', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create(): View
    {
        $eventId = $this->eventService->requireActiveEventId();

        return view('participants.create', [
            'categories' => ParticipantCategory::where('event_id', $eventId)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreParticipantRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('participants', 'public');
        }

        $this->participantService->create($data);

        return redirect()->route('participants.index')
            ->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function show(Participant $participant): View
    {
        $participant = $this->participantService->findForEvent($participant->id);

        return view('participants.show', compact('participant'));
    }

    public function edit(Participant $participant): View
    {
        $participant = $this->participantService->findForEvent($participant->id);
        $eventId = $this->eventService->requireActiveEventId();

        return view('participants.edit', [
            'participant' => $participant,
            'categories' => ParticipantCategory::where('event_id', $eventId)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateParticipantRequest $request, Participant $participant): RedirectResponse
    {
        $participant = $this->participantService->findForEvent($participant->id);
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('participants', 'public');
        }

        $this->participantService->update($participant, $data);

        return redirect()->route('participants.index')
            ->with('success', 'Peserta berhasil diperbarui.');
    }

    public function destroy(Participant $participant): RedirectResponse
    {
        $participant = $this->participantService->findForEvent($participant->id);
        $this->participantService->delete($participant);

        return redirect()->route('participants.index')
            ->with('success', 'Peserta berhasil dihapus.');
    }

    public function export(): BinaryFileResponse
    {
        $eventId = $this->eventService->requireActiveEventId();

        return Excel::download(
            new ParticipantsExport($eventId),
            'peserta-'.now()->format('Y-m-d').'.xlsx',
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $eventId = $this->eventService->requireActiveEventId();

        Excel::import(
            new ParticipantsImport($eventId, $this->activityLogService),
            $request->file('file'),
        );

        return redirect()->route('participants.index')
            ->with('success', 'Peserta berhasil diimport dari Excel.');
    }
}
