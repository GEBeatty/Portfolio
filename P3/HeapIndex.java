/* A hash table used to keep track of the different objects within a heap. Used for
 * updating and deleting different parts of a heap.
 */
class HeapIndex
{
    NodeChain[] index;
    int mod;    // The hash number

    public HeapIndex()
    {
        index = new NodeChain[23];
        Arrays.fill(index, null);
        mod = 23;
    }

    // Adds to the current index for that heap
    public void addToIndex(CarNode c)
    {
        // First, create a new IndexNode
        IndexNode newIndex = new IndexNode(c);
        // Next, hash in order to find a spot
        int spot = hash(c.vin);
        // If there isn't a valid chain yet, create one
        if(index[spot] == null){
            index[spot] = new NodeChain(newIndex);
        } else { // Otherwise, add to the chain
            index[spot].addToChain(newIndex);
        }
    }

    // Find the indicies of a car using a VIN
    public CarNode findIndex(String v)
    {
        CarNode found = null;
        // Now hash the VIN and find to find the index
        int vHash = hash(v);
        // If there's nothing in that spot, the car isn't in the index
        if(index[vHash] == null){
            // We return the node
        } else {
            found = index[vHash].searchTheChain(v);
        }
        return found;
    }

    public void deleteIndex(CarNode c)
    {
        // Hash the VIN and find to find the index
        int vHash = hash(c.vin);
        // Now we need to find and replace the node
        index[vHash].deleteFromChain(c.vin);
    }

    public int hash(String s)
    {
        return (s.hashCode() & 0x7fffffff) % mod;
    }
}

class NodeChain
{
    IndexNode root;

    public NodeChain(IndexNode i)
    {
        root = i;
    }

    // Add an IndexNode to the current NodeChain
    public void addToChain(IndexNode i)
    {
        if(i == null){
            return;
        }
        addChain(root, i);
    }
    // The recursive addChain method, follows the addToChain method
    public void addChain(IndexNode start, IndexNode i)
    {
        // If the next node is null, place our new node there
        if(start == null){
            start = i;
        } else { // Otherwise, keep going
            addChain(start.next, i);
        }
    }
    
    // Used to find a particular VIN in a NodeChain
    public CarNode searchTheChain(String v)
    {
        CarNode c;
        if(v == null){ // The string is null
            c = null;
        } else if(root == null){ // The root is null, nothing is here
            c = null;
        } else {
            c = searchChain(root, v);
        }
        return c;
    }
    // Recursive partner function to searchTheChain
    public CarNode searchChain(IndexNode i, String v)
    {
        if(i.car.vin.equals(v)){ // If the VIN is equal, we found it
            return i.car;
        } else if (i.next == null) { // It's not here
            return null;
        } // Otherwise check the next one
        CarNode c = searchChain(i.next, v);
        return c;
    }

    public void deleteFromChain(String v)
    {
        if(v == null){
            System.out.println("VIN is null");
        } else if(root.car.vin.equals(v)){ // The root is it
            if(root.next != null){ // Replace the root
                root = root.next;
            } else { // Get rid of the whole thing
                root = null;
            }
        } else {
            deleteChain(root, v);
        }
    }
    // Recursive partner function to deleteFromChain
    public void deleteChain(IndexNode i, String v)
    {
        if(i.next.car.vin.equals(v)){ // The next node is the one
            if(i.next.next != null){  // Replace it with the one after
                i.next = i.next.next;
            } else {
                i.next = null;
            }
        } else if(i.next.next == null){
            // Done
        } else {
            deleteChain(i.next, v);
        }
    }
}

// A specialized node for the HeapIndex
class IndexNode
{
    CarNode car;    // Reference to a car node
    IndexNode next;   // Reference to the next node in the chain

    public IndexNode(CarNode c)
    {
        car = c;
        next = null;
    }

    public CarNode getCar()
    {
        return car;
    }
}