<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\API\APIController as APIController;
use App\Models\Note;
use Illuminate\Http\Response;
use App\Http\Resources\Note as NoteResource;
use Illuminate\Support\Facades\Validator;

class NoteController extends APIController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = auth('api')->user();
        $notes = $user->notes;

        return $this->sendResponse(NoteResource::collection($notes), 'Notes retrieved successfully.');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = auth('api')->user();
        $input['user_id'] = $user->id;

        $note = Note::create($input);

        return $this->sendResponse(new NoteResource($note), 'Note created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $note = Note::find($id);

        if (is_null($note))
        {
            return $this->sendError('Note not found.');
        }

        $user = auth('api')->user();

        if (is_null($note->user) || ($user->id != $note->user->id))
        {
            return $this->sendError('Forbidden.', [], 403);
        }

        return $this->sendResponse(new NoteResource($note), 'Note retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Note $note
     * @return JsonResponse
     */
    public function update(Request $request, Note $note): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = auth('api')->user();

        if (is_null($note->user) || ($user->id != $note->user->id))
        {
            return $this->sendError('Forbidden.', [], 403);
        }

        $note->title = $input['title'];
        $note->content = $input['content'];
        $note->save();

        return $this->sendResponse(new NoteResource($note), 'Note updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Note $note
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Note $note): JsonResponse
    {
        $user = auth('api')->user();

        if (is_null($note->user) || ($user->id != $note->user->id))
        {
            return $this->sendError('Forbidden.', [], 403);
        }

        $note->delete();

        return $this->sendResponse([], 'Note deleted successfully.');
    }
}
