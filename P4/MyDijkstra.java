/**
 * Personalized version of Princeton's DijkstraSP.java for the network
 * project 4 of CS1501
 */
public class MyDijkstra
{
    double[] distTo;        // distTo[v] = distance  of shortest s->v path
    NetworkEdge[] edgeTo;   // edgeTo[v] = last edge on shortest s->v path
    IndexMinPQ<Double> pq;  // priority queue of vertices

    public MyDijkstra(MyGraph G, int s) {
        // Create my arrays for my distances and edges
        distTo = new double[G.V()];
        edgeTo = new NetworkEdge[G.V()];

        // Set all of my distances to positive infinity
        for (int v = 0; v < G.V(); v++)
            distTo[v] = Double.POSITIVE_INFINITY;
        distTo[s] = 0.0;

        // relax vertices in order of distance from s
        pq = new IndexMinPQ<Double>(G.V());
        pq.insert(s, distTo[s]);
        while (!pq.isEmpty()) {
            int v = pq.delMin();
            for (NetworkEdge e : G.adj(v))
                relax(e);
        }

        // check optimality conditions
        assert check(G, s);
    }

    /**
     * Meant to create a minimum spanning tree using only the copper edges provided
     * by the graph
     * @param G The graph
     * @param s The starting vertex
     * @param b Bogey variable used to call this class
     */
    public MyDijkstra(MyGraph G, int s, boolean b) {
        // Create my arrays for my distances and edges
        distTo = new double[G.V()];
        edgeTo = new NetworkEdge[G.V()];

        // Set all of my distances to positive infinity
        for (int v = 0; v < G.V(); v++)
            distTo[v] = Double.POSITIVE_INFINITY;
        distTo[s] = 0.0;

        // relax vertices in order of distance from s
        pq = new IndexMinPQ<Double>(G.V());
        pq.insert(s, distTo[s]);
        while (!pq.isEmpty()) {
            int v = pq.delMin();
            for (NetworkEdge e : G.adj(v))
                copperRelax(e);
        }

        // check optimality conditions
        assert check(G, s);
    }

    // relax edge e and update pq if changed
    private void relax(NetworkEdge e) {
        int v = e.from(), w = e.to();
        if (distTo[w] > distTo[v] + e.weight()) {
            distTo[w] = distTo[v] + e.weight();
            edgeTo[w] = e;
            if (pq.contains(w)) pq.decreaseKey(w, distTo[w]);
            else                pq.insert(w, distTo[w]);
        }
    }

    // relax only copper edges e and update pq if changed
    private void copperRelax(NetworkEdge e) {
        if(e.type().equalsIgnoreCase("copper")){
            int v = e.from(), w = e.to();
            if (distTo[w] > distTo[v] + e.weight()) {
                distTo[w] = distTo[v] + e.weight();
                edgeTo[w] = e;
                if (pq.contains(w)) pq.decreaseKey(w, distTo[w]);
                else                pq.insert(w, distTo[w]);
            }
        }
        // Otherwise it's optical, and we ignore it
    }

    public NetworkEdge[] edges() {
        NetworkEdge[] arr = new NetworkEdge[edgeTo.length];
        int i = 0;
        for (NetworkEdge var : edgeTo ) {
            arr[i] = var;
            i++;
        }
        return arr;
    }

    /**
     * Returns the length of a shortest path from the source vertex {@code s} to vertex {@code v}.
     * @param  v the destination vertex
     * @return the length of a shortest path from the source vertex {@code s} to vertex {@code v};
     *         {@code Double.POSITIVE_INFINITY} if no such path
     * @throws IllegalArgumentException unless {@code 0 <= v < V}
     */
    public double distTo(int v) {
        return distTo[v];
    }

    /**
     * Returns true if there is a path from the source vertex {@code s} to vertex {@code v}.
     *
     * @param  v the destination vertex
     * @return {@code true} if there is a path from the source vertex
     *         {@code s} to vertex {@code v}; {@code false} otherwise
     * @throws IllegalArgumentException unless {@code 0 <= v < V}
     */
    public boolean hasPathTo(int v) {
        return distTo[v] < Double.POSITIVE_INFINITY;
    }

    /**
     * Returns a shortest path from the source vertex {@code s} to vertex {@code v}.
     *
     * @param  v the destination vertex
     * @return a shortest path from the source vertex {@code s} to vertex {@code v}
     *         as an iterable of edges, and {@code null} if no such path
     * @throws IllegalArgumentException unless {@code 0 <= v < V}
     */
    public Iterable<NetworkEdge> pathTo(int v) {
        if (!hasPathTo(v)) return null;
        Stack<NetworkEdge> path = new Stack<NetworkEdge>();
        for (NetworkEdge e = edgeTo[v]; e != null; e = edgeTo[e.from()]) {
            path.push(e);
        }
        return path;
    }

    /**
     * Test to see whether all of the vertices in a graph are connected
     * @return true if the graph is connected
     */
    public boolean connected() {
        for (double var : distTo) {
            if (var == Double.POSITIVE_INFINITY) return false;
        }
        return true;
    }

    private boolean check(MyGraph G, int s) {
        // check that distTo[v] and edgeTo[v] are consistent
        if (distTo[s] != 0.0 || edgeTo[s] != null) {
            System.err.println("distTo[s] and edgeTo[s] inconsistent");
            return false;
        }
        for (int v = 0; v < G.V(); v++) {
            if (v == s) continue;
            if (edgeTo[v] == null && distTo[v] != Double.POSITIVE_INFINITY) {
                System.err.println("distTo[] and edgeTo[] inconsistent");
                return false;
            }
        }

        // check that all edges e = v->w satisfy distTo[w] <= distTo[v] + e.weight()
        for (int v = 0; v < G.V(); v++) {
            for (NetworkEdge e : G.adj(v)) {
                int w = e.to();
                if (distTo[v] + e.weight() < distTo[w]) {
                    System.err.println("edge " + e + " not relaxed");
                    return false;
                }
            }
        }

        // check that all edges e = v->w on SPT satisfy distTo[w] == distTo[v] + e.weight()
        for (int w = 0; w < G.V(); w++) {
            if (edgeTo[w] == null) continue;
            NetworkEdge e = edgeTo[w];
            int v = e.from();
            if (w != e.to()) return false;
            if (distTo[v] + e.weight() != distTo[w]) {
                System.err.println("edge " + e + " on shortest path not tight");
                return false;
            }
        }
        return true;
    }
}