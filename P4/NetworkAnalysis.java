import java.io.*;
import java.util.Scanner;
import java.util.InputMismatchException;
import java.util.Iterator;

/**
 * Grace Beatty
 * CS 1501 Project 4 - Graphs
 * 
 * Started: 3/27/19
 * Last Modified: 4/4/19
 */

public class NetworkAnalysis
{
    public static void main(String[] args)
    {
        String networkFilename = args[0];
        MyGraph graph = null;
        SimpleGraph sGraph = null;
        try{
            // Open the file to read in the graph components
            File networkFile = new File(networkFilename);
            Scanner fileScan = new Scanner(networkFile);
            // Get the number of edges
            String input = fileScan.nextLine();
            int numVertices = Integer.parseInt(input);
            // nextLine array
            String[] arr = new String[5];
            // Initialize the adjacency lists
            graph = new MyGraph(numVertices);
            sGraph = new SimpleGraph(numVertices);
            // Grab all of the edges from the file
            while(fileScan.hasNext()){
                input = fileScan.nextLine();
                arr = input.split(" ");
                // Add the split parts of the next line to a new NetworkEdge
                NetworkEdge n = new NetworkEdge(Integer.parseInt(arr[0]), 
                                                Integer.parseInt(arr[1]), 
                                                arr[2],
                                                Integer.parseInt(arr[3]),
                                                Double.parseDouble(arr[4]));
                // System.out.println("This edge: " + n.from() + " -> " + n.to());
                graph.addEdge(n.from(), n);
                sGraph.addEdge(n.from(), n.to());
                // Create second edge for a digraph representation
                n = new NetworkEdge(Integer.parseInt(arr[1]), 
                                                Integer.parseInt(arr[0]), 
                                                arr[2],
                                                Integer.parseInt(arr[3]),
                                                Double.parseDouble(arr[4]));
                graph.addEdge(n.from(), n);
                sGraph.addEdge(n.from(), n.to());
            }
            fileScan.close();
        } catch (IOException e){
            System.out.println("Sorry, there was an error loading the file: \n" + e + "\n");
        }
        // System.out.println("Done!");
        // graph.displayConnections();
        // sGraph.displayConnections();
        System.out.println();

        int input = 0;
        Scanner userInput = new Scanner(System.in);
        // MENU
        while(input != 5){
            System.out.println("What would you like to do?");
            System.out.print("1) Find lowest latency between 2 points\n"+
                            "2) Is graph copper-only connected?\n"+
                            "3) Determine average latency MST\n"+
                            "4) Determine graph failure susceptibility\n"+
                            "5) Quit\n"+
                            " > ");
            input = userInput.nextInt();
            
            // MENU SELECTIONS
            if(input == 1){ // 1) Lowest latency
                int start, end;
                // Get first
                System.out.print("Starting point > ");
                start = userInput.nextInt();
                // Get second
                System.out.print("Ending point   > ");
                end = userInput.nextInt();
                // Consult Dijkstra
                MyDijkstra md = new MyDijkstra(graph, start);
                int lowestBandwidth = Integer.MAX_VALUE;
                if(md.hasPathTo(end)){
                    // Get edge iterator
                    Iterable<NetworkEdge> path = md.pathTo(end);
                    Iterator itrStack = path.iterator();
                    System.out.println("Path from " + start + " to " + end + ":");
                    while(itrStack.hasNext())
                    {
                        NetworkEdge e = (NetworkEdge) itrStack.next();
                        // Test bandwidth for lowest
                        if(e.bandwidth() < lowestBandwidth){
                            lowestBandwidth = e.bandwidth();
                        }
                        // Print out next portion
                        System.out.print(e.from() + " -> ");
                        if(e.to() == end){
                            System.out.print(e.to());
                        }
                    }
                    System.out.println();
                    System.out.println("Bandwidth available: " + lowestBandwidth + " Mbps");
                } else {
                    System.out.println("There is no path between " + start + " and " + end);
                }
            } else if (input == 2){ // 2) Copper-only
                MyDijkstra cp = new MyDijkstra(graph, 0, true);
                if(cp.connected()){
                    System.out.println("Yes, the graph has copper-only capabilities");
                } else {
                    System.out.println("No, the graph requires fiber cable for optimum usage");
                }
            } else if (input == 3){ // 3) Average latency MST
                PrimMST primmy = new PrimMST(graph);
                if(!primmy.connectedTree()){
                    System.out.println("This graph is not a single spanning tree");
                } else {
                    Iterable<NetworkEdge> pm = primmy.edges();
                    // Iterator<NetworkEdge> itrPrim = pm.iterator();
                    int numE = 0;
                    double avgL = 0;
                    // Go through the edges in the MST
                    System.out.println("Edges in the MST: ");
                    for (NetworkEdge e : pm) {
                        System.out.println(e.toString());
                        avgL = avgL + e.weight();
                        numE+=1;
                    }
                    System.out.println("   Average graph latency: " + (avgL/numE)); 
                }                                               
            } else if (input == 4){ // 4) Graph vertice failure
                System.out.println("Scanning system . . .");
                for(int k = 0; k < sGraph.V(); k++){    // Outer loop
                    int s = 0;
                    for(int i = k+1; i < sGraph.V(); i++){// Inner loop
                        s = 0;
                        if(k == 0) s = 1;
                        if(i == 1) s = 2;
                        if(s < (sGraph.V() - 1)){
                            DepthFirstSearch dfs = new DepthFirstSearch(sGraph, s, k, i);
                            if(!dfs.connected()){
                                System.out.println("Vulnerability at pair " + k + ", " + i);
                            }
                        }
                    }
                }                
                System.out.println("Scan complete");
            } else if (input == 5){ // 5) Quit
                // Quit option: do nothing
            } else {
                System.out.println("Please choose a valid answer");
            }
            System.out.println("----------------------");
        }
        userInput.close();
        System.out.println("\n\tGoodbye\n");
    }
}

class SimpleGraph
{
    int vertices;
    int edges;
    Bag<Integer>[] list;

    public SimpleGraph(int v)
    {
        vertices = v;
        edges = 0;
        list = (Bag<Integer>[]) new Bag[v];
        for(int i = 0; i < v; i++){
            list[i] = new Bag<Integer>();
        }
    }

    public void addEdge(int v, int b)
    {
        // Add the edge to the appropriate vertex
        Integer n = new Integer(b);
        list[v].add(n);
        edges++; // Add another edge
    }

    public Iterable<Integer> adj(int v)
    {
        return list[v];
    }

    public void displayConnections() 
    {
        System.out.println("Simple Connections: ");
        // Vertex outer loop
        for(int i = 0; i < list.length; i++){
            System.out.print("Vertex " + i + ": ");
            // Need an iterator
            Iterator<Integer> itr = list[i].iterator();
            while(itr.hasNext())
            {
                Integer m = itr.next();
                System.out.print(m.toString() + " ");
            }
            System.out.println();
        }
    }

    public int V(){ return vertices; }
    public int E(){ return edges; }
}

class MyGraph
{
    // ArrayList<ArrayList<NetworkEdge>> list;
    int vertices;
    int edges;
    Bag<NetworkEdge>[] list;

    public MyGraph(int v)
    {
        vertices = v;
        edges = 0;
        list = (Bag<NetworkEdge>[]) new Bag[v];
        // Fill ArrayList with lists ready to go
        for(int i = 0; i < v; i++){
            list[i] = new Bag<NetworkEdge>();
        }
    }

    public void addEdge(int v, NetworkEdge e)
    {
        // Add the edge to the appropriate vertex
        list[v].add(e);
        edges++; // Add another edge
    }

    /**
     * Get method
     * @return number of vertices
     */
    public int V(){
        return vertices;
    }
    /**
     * Get method
     * @return number of edges
     */
    public int E(){
        return edges;
    }

    /**
     * Create and send an iterable version of a neighbor list at vertex v
     * @param v the vertex
     * @return an iterable of v's neighbors
     */
    public Iterable<NetworkEdge> adj(int v)
    {
        return list[v];
    }

    /**
     * Display the graph connections one vertex at a time
     */
    public void displayConnections()
    {
        System.out.println("Connections: ");
        // Vertex outer loop
        for(int i = 0; i < list.length; i++){
            System.out.print("Vertex " + i + ": ");
            // Need an iterator
            Iterator<NetworkEdge> itr = list[i].iterator();
            while(itr.hasNext())
            {
                NetworkEdge e = itr.next();
                System.out.print(e.to() + " ");
            }
            System.out.println();
        }
    }
}


/*
 * Class used to represent an edge in the graph
 */
class NetworkEdge
{
    int point_1;    // Endpoint 1
    int point_2;    // Endpoint 2
    String type;    // Type of wire
    int bandwidth;
    double length;
    double latency;

    public NetworkEdge(int p1, int p2, String t, int b, double l)
    {
        point_1 = p1; point_2 = p2;
        type = t; bandwidth = b; length = l;
        
        // Copper
        if(type.equals("copper")){
            latency = length / 230000000;
        // Light
        } else {
            latency = length / 200000000;
        }
    }

    /**
     * Series of get functions
     */
    public int from()
    {
        return point_1;
    }
    public int to()
    {
        return point_2;
    }
    public double weight()
    {
        return latency;
    }
    public int bandwidth()
    {
        return bandwidth;
    }
    public String type()
    {
        return type;
    }

    /**
     * Other method returns the other vertex that's part of the pair
     */
    public int other(int v)
    {
        if(v == point_1) return point_2;
        return point_1;
    }

    /**
     * toString method
     */
    public String toString()
    {
        String s = ("Edge " + point_1 + " -> " + point_2);
        s = s + "\nBandwidth: " + bandwidth;
        s = s + "\nLatency: " + latency;
        return s;
    }

    /**
     * compareTo method, used to compare latencies between edges
     */
    public int compareTo(NetworkEdge e)
    {
        // Return 1 if this NetworkEdge has lower latency
        if(this.latency < e.latency){
            return 1;
        }
        return 0;
    }
}

