<?php

namespace App\Services;

use App\Models\Tickets;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

class TicketService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get tickets for DataTable with filters
     */
    public function getTicketsForDataTable(array $filters = [])
    {
        try {
            $query = Tickets::query();

            // Apply filters
            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('ticket_id', 'like', '%' . $search . '%');
                });
            }

            if (isset($filters['doctor_id'])) {
                $query->where('doctor_id', $filters['doctor_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['priority'])) {
                $query->where('priority', $filters['priority']);
            }

            if (isset($filters['assigned_to'])) {
                $query->where('assigned_to', $filters['assigned_to']);
            }

            return $query->with(['doctor', 'assignedUser'])->orderBy('created_at', 'desc');
        } catch (Exception $e) {
            Log::error('Error getting tickets for DataTable: ' . $e->getMessage());
            throw new Exception('Failed to retrieve tickets: ' . $e->getMessage());
        }
    }

    /**
     * Create a new ticket
     */
    public function createTicket(array $data, $doctorId)
    {
        DB::beginTransaction();
        try {
            $data['ticket_id'] = $this->generateTicketId();
            $data['doctor_id'] = $doctorId;
            $data['status'] = $data['status'] ?? 'open';

            $ticket = Tickets::create($data);

            DB::commit();
            return $ticket;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating ticket: ' . $e->getMessage());
            throw new Exception('Failed to create ticket: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing ticket
     */
    public function updateTicket($id, array $data)
    {
        try {
            $ticket = Tickets::findOrFail($id);
            
            $ticket->update($data);
            
            return $ticket;
        } catch (Exception $e) {
            Log::error('Error updating ticket: ' . $e->getMessage());
            throw new Exception('Failed to update ticket: ' . $e->getMessage());
        }
    }

    /**
     * Delete a ticket
     */
    public function deleteTicket($id)
    {
        try {
            $ticket = Tickets::findOrFail($id);
            $ticket->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Error deleting ticket: ' . $e->getMessage());
            throw new Exception('Failed to delete ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get ticket by ID
     */
    public function getTicketById($id)
    {
        try {
            return Tickets::with(['doctor', 'assignedUser', 'comments'])
                         ->findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error getting ticket by ID: ' . $e->getMessage());
            throw new Exception('Ticket not found');
        }
    }

    /**
     * Change ticket status
     */
    public function changeTicketStatus($id, $status)
    {
        try {
            $ticket = Tickets::findOrFail($id);
            
            $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid ticket status');
            }

            $ticket->update(['status' => $status]);
            
            return $ticket;
        } catch (Exception $e) {
            Log::error('Error changing ticket status: ' . $e->getMessage());
            throw new Exception('Failed to change ticket status: ' . $e->getMessage());
        }
    }

    /**
     * Change ticket priority
     */
    public function changeTicketPriority($id, $priority)
    {
        try {
            $ticket = Tickets::findOrFail($id);
            
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($priority, $validPriorities)) {
                throw new Exception('Invalid ticket priority');
            }

            $ticket->update(['priority' => $priority]);
            
            return $ticket;
        } catch (Exception $e) {
            Log::error('Error changing ticket priority: ' . $e->getMessage());
            throw new Exception('Failed to change ticket priority: ' . $e->getMessage());
        }
    }

    /**
     * Close a ticket
     */
    public function closeTicket($id)
    {
        try {
            $ticket = Tickets::findOrFail($id);
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now()
            ]);
            
            return $ticket;
        } catch (Exception $e) {
            Log::error('Error closing ticket: ' . $e->getMessage());
            throw new Exception('Failed to close ticket: ' . $e->getMessage());
        }
    }

    /**
     * Reopen a ticket
     */
    public function reopenTicket($id)
    {
        try {
            $ticket = Tickets::findOrFail($id);
            $ticket->update([
                'status' => 'open',
                'closed_at' => null
            ]);
            
            return $ticket;
        } catch (Exception $e) {
            Log::error('Error reopening ticket: ' . $e->getMessage());
            throw new Exception('Failed to reopen ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get ticket statistics for doctor
     */
    public function getTicketStats($doctorId)
    {
        try {
            $totalTickets = Tickets::where('doctor_id', $doctorId)->count();
            $openTickets = Tickets::where('doctor_id', $doctorId)
                                 ->where('status', 'open')
                                 ->count();
            $inProgressTickets = Tickets::where('doctor_id', $doctorId)
                                      ->where('status', 'in_progress')
                                      ->count();
            $closedTickets = Tickets::where('doctor_id', $doctorId)
                                   ->where('status', 'closed')
                                   ->count();
            $urgentTickets = Tickets::where('doctor_id', $doctorId)
                                   ->where('priority', 'urgent')
                                   ->whereIn('status', ['open', 'in_progress'])
                                   ->count();

            return [
                'total_tickets' => $totalTickets,
                'open_tickets' => $openTickets,
                'in_progress_tickets' => $inProgressTickets,
                'closed_tickets' => $closedTickets,
                'urgent_tickets' => $urgentTickets,
            ];
        } catch (Exception $e) {
            Log::error('Error getting ticket stats: ' . $e->getMessage());
            throw new Exception('Failed to retrieve ticket statistics');
        }
    }

    /**
     * Generate unique ticket ID
     */
    private function generateTicketId()
    {
        do {
            $ticketId = 'TKT-' . strtoupper(Str::random(8));
        } while (Tickets::where('ticket_id', $ticketId)->exists());

        return $ticketId;
    }

    /**
     * Assign ticket to user
     */
    public function assignTicket($ticketId, $userId)
    {
        try {
            $ticket = Tickets::findOrFail($ticketId);
            $user = User::findOrFail($userId);
            
            $ticket->update(['assigned_to' => $userId]);
            
            return $ticket;
        } catch (Exception $e) {
            Log::error('Error assigning ticket: ' . $e->getMessage());
            throw new Exception('Failed to assign ticket: ' . $e->getMessage());
        }
    }

    /**
     * Get tickets by status
     */
    public function getTicketsByStatus($doctorId, $status)
    {
        try {
            return Tickets::where('doctor_id', $doctorId)
                         ->where('status', $status)
                         ->with(['assignedUser'])
                         ->orderBy('created_at', 'desc')
                         ->get();
        } catch (Exception $e) {
            Log::error('Error getting tickets by status: ' . $e->getMessage());
            throw new Exception('Failed to retrieve tickets by status');
        }
    }

    /**
     * Get tickets by priority
     */
    public function getTicketsByPriority($doctorId, $priority)
    {
        try {
            return Tickets::where('doctor_id', $doctorId)
                         ->where('priority', $priority)
                         ->with(['assignedUser'])
                         ->orderBy('created_at', 'desc')
                         ->get();
        } catch (Exception $e) {
            Log::error('Error getting tickets by priority: ' . $e->getMessage());
            throw new Exception('Failed to retrieve tickets by priority');
        }
    }
}

